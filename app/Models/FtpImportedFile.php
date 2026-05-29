<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtpImportedFile extends Model
{
    protected $fillable = ['ftp_server_id', 'race_id', 'filename'];

    public function server(): BelongsTo
    {
        return $this->belongsTo(FtpServer::class, 'ftp_server_id');
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}