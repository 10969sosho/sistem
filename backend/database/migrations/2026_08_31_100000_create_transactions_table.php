<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['pemasukan', 'pengeluaran']);
            $table->string('category');
            $table->string('description')->nullable();
            $table->string('vendor')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['paid', 'pending', 'cancelled'])->default('paid');
            $table->string('payment_method')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
