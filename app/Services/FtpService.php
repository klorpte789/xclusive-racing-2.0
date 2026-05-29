<?php

namespace App\Services;

use App\Models\FtpServer;

class FtpService
{
    private mixed $connection = null;

    public function connect(FtpServer $server): bool
    {
        if (!extension_loaded('ftp')) {
            return false;
        }

        set_error_handler(static fn() => null);
        $conn = ftp_connect($server->host, $server->port, 10);
        restore_error_handler();

        if (!$conn) {
            return false;
        }

        $loggedIn = @ftp_login($conn, $server->username, $server->password);

        if (!$loggedIn) {
            @ftp_close($conn);
            return false;
        }

        ftp_pasv($conn, true);
        $this->connection = $conn;

        return true;
    }

    /**
     * Returns [
     *   'json' => [['name' => '...', 'size' => 12345, 'modified' => '...'], ...],
     *   'all'  => ['file1.json', 'cfg.ini', ...]   (for debug display)
     * ]
     */
    public function listFiles(string $path): array
    {
        if (!$this->connection) {
            return ['json' => [], 'all' => []];
        }

        $raw = $this->tryList($path);

        if ($raw === null) {
            return ['json' => [], 'all' => []];
        }

        $all = array_values(array_filter(
            array_map('basename', $raw),
            fn($f) => $f !== '' && $f !== '.' && $f !== '..'
        ));

        // Metadata (size, modified) from rawlist — one call for all files
        $meta = $this->parseRawList($path);

        $json = [];
        foreach ($all as $filename) {
            $lower = strtolower($filename);

            if (!str_ends_with($lower, '.json')) continue;
            if (str_contains($lower, '_fp_') || str_ends_with($lower, '_fp.json')) continue;
            if (str_contains($lower, 'entrylist')) continue;

            $json[] = [
                'name'     => $filename,
                'size'     => $meta[$filename]['size'] ?? null,
                'modified' => $meta[$filename]['modified'] ?? null,
            ];
        }

        // Newest first — YYMMDD_HHMMSS prefix makes lexicographic sort correct
        usort($json, fn($a, $b) => strcmp($b['name'], $a['name']));

        return ['json' => $json, 'all' => $all];
    }

    /**
     * Parses ftp_rawlist output into ['filename' => ['size' => int, 'modified' => string]].
     * Handles both Unix-style and Windows/IIS style listings.
     */
    private function parseRawList(string $path): array
    {
        $lines = @ftp_rawlist($this->connection, $path);
        if (!$lines) {
            return [];
        }

        $result = [];

        foreach ($lines as $line) {
            // Unix style: -rw-r--r-- 1 user group 45632 May 28 12:34 filename.json
            if (preg_match(
                '/^[d\-][rwx\-]{9}\S*\s+\d+\s+\S+\s+\S+\s+(\d+)\s+(\w+\s+\d+\s+[\d:]+)\s+(.+)$/',
                $line, $m
            )) {
                $result[basename(trim($m[3]))] = [
                    'size'     => (int) $m[1],
                    'modified' => trim($m[2]),
                ];
                continue;
            }

            // Windows/IIS style: 05-29-26  02:34PM  45632 filename.json
            if (preg_match('/^(\d{2}-\d{2}-\d{2}\s+\d{2}:\d{2}[AP]M)\s+(\d+)\s+(.+)$/', $line, $m)) {
                $result[basename(trim($m[3]))] = [
                    'size'     => (int) $m[2],
                    'modified' => trim($m[1]),
                ];
            }
        }

        return $result;
    }

    /**
     * Tries multiple strategies to list a directory.
     * GPortal FTP servers behave differently depending on server type/version.
     */
    private function tryList(string $path): ?array
    {
        $candidates = array_unique([
            $path,
            '/' . ltrim($path, '/'),
            rtrim($path, '/'),
            '/' . rtrim(ltrim($path, '/'), '/'),
        ]);

        foreach ($candidates as $p) {
            // Strategy A: chdir then list current dir
            if (@ftp_chdir($this->connection, $p)) {
                $files = @ftp_nlist($this->connection, '.');
                if (is_array($files) && count($files) > 0) {
                    return $files;
                }
            }

            // Strategy B: nlist with explicit path
            $files = @ftp_nlist($this->connection, $p);
            if (is_array($files) && count($files) > 0) {
                return $files;
            }
        }

        // Strategy C: rawlist fallback (parses LIST output, last token = filename)
        foreach ($candidates as $p) {
            $raw = @ftp_rawlist($this->connection, $p);
            if (is_array($raw) && count($raw) > 0) {
                return array_map(function (string $line): string {
                    preg_match('/(\S+)\s*$/', $line, $m);
                    return $m[1] ?? '';
                }, $raw);
            }
        }

        return null;
    }

    public function getFileContent(string $fullPath): string|false
    {
        if (!$this->connection) {
            return false;
        }

        $tmp     = tmpfile();
        $success = @ftp_fget($this->connection, $tmp, $fullPath, FTP_BINARY);

        if (!$success) {
            fclose($tmp);
            return false;
        }

        rewind($tmp);
        $content = stream_get_contents($tmp);
        fclose($tmp);

        return $content;
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            @ftp_close($this->connection);
            $this->connection = null;
        }
    }

    public static function parseFilename(string $filename): array
    {
        $name  = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $name);

        $date    = '—';
        $session = 'Unknown';

        if (count($parts) >= 3) {
            $datePart = $parts[0];
            $timePart = $parts[1] ?? '';
            $typePart = strtoupper($parts[2] ?? '');

            if (strlen($datePart) === 6 && is_numeric($datePart)) {
                $y    = '20' . substr($datePart, 0, 2);
                $m    = substr($datePart, 2, 2);
                $d    = substr($datePart, 4, 2);
                $h    = substr($timePart, 0, 2);
                $i    = substr($timePart, 2, 2);
                $date = "$d/$m/$y $h:$i";
            }

            if ($typePart === 'R') {
                $session = 'Race';
            } elseif ($typePart === 'Q') {
                $session = 'Qualifying';
            }
        }

        return compact('date', 'session');
    }
}