<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tokens_descarga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_id')->constrained('pdfs')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('fecha_expiracion');
            $table->boolean('usado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_descarga');
    }
};
