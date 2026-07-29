<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attack_logs', function (Blueprint $table) {

            $table->id();

            $table->string('ip_address');

            $table->string('route')->nullable();

            $table->text('payload');

            $table->string('pattern');

            $table->string('method');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attack_logs');
    }
};