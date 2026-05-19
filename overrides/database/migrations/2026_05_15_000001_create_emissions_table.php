<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emissions', function (Blueprint $table) {
            $table->id();
            $table->string('doc_id');
            $table->string('serie')->nullable();
            $table->string('numero')->nullable();
            $table->string('tipo')->nullable();
            $table->string('status', 20)->default('OK');
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->text('url_acepta')->nullable();
            $table->text('hash')->nullable();
            $table->text('firma')->nullable();
            $table->longText('xml_enviado')->nullable();
            $table->text('respuesta_raw')->nullable();
            $table->string('comando', 50)->default('emitir');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emissions');
    }
};
