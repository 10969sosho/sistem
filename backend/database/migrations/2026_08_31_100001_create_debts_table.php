<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->enum('type', ['talangan', 'pinjaman', 'reimburse']);
            $table->string('person'); // CECIL or TIAN
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['belum_dibayar', 'dibayar_sebagian', 'lunas'])->default('belum_dibayar');
            $table->date('paid_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
