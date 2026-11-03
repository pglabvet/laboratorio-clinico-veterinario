<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cada pago (total o parcial) de un cobro. Los pagos no se editan:
     * si hay un error se anulan y se registra uno nuevo.
     */
    public function up(): void
    {
        Schema::create('pagos_cobro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobro_id')->constrained('cobros')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('metodo', 20); // QR, Efectivo, Tarjeta
            $table->timestamp('fecha_pago')->useCurrent();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_at')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['cobro_id', 'anulado_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_cobro');
    }
};
