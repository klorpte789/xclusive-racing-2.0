<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ftp_imported_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftp_server_id')->nullable()->constrained('ftp_servers')->nullOnDelete();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->timestamps();
            $table->unique(['race_id', 'filename']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftp_imported_files');
    }
};