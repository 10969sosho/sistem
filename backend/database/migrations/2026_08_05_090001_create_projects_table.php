<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'progress', 'testing', 'revisi', 'maintenance', 'selesai'])
                ->default('pending')->index();
            $table->date('deadline')->nullable()->index();
            $table->string('pic')->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
