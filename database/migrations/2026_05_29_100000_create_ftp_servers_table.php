<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ftp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(21);
            $table->string('username');
            $table->text('password');
            $table->string('path')->default('/results');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftp_servers');
    }
};