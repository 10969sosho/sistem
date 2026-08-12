<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('dp', 15, 2)->default(0);
            $table->decimal('termin1', 15, 2)->default(0);
            $table->decimal('termin2', 15, 2)->default(0);
            $table->decimal('termin3', 15, 2)->default(0);
            $table->decimal('pelunasan', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
