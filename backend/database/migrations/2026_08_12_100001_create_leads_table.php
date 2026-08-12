<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('source')->default('other')->index();
            $table->text('requirement');
            $table->text('notes')->nullable();
            $table->date('entered_at')->index();
            $table->string('status')->default('new')->index();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->date('deal_date')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
