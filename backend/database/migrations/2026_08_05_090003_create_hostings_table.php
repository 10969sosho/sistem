<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            // Hosting
            $table->string('provider')->nullable();
            $table->string('package')->nullable();
            $table->date('expired_date')->nullable()->index();
            // Domain
            $table->string('domain')->nullable()->index();
            $table->string('registrar')->nullable();
            $table->date('domain_expired_date')->nullable()->index();
            // SSL
            $table->enum('ssl_status', ['active', 'non_active'])->nullable();
            $table->date('ssl_expired_date')->nullable();
            // Server
            $table->string('server_ip')->nullable();
            $table->string('panel')->nullable();
            $table->string('username')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostings');
    }
};
