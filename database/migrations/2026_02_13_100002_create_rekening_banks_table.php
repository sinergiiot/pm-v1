<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->unique();
            $table->string('account_number')->unique();
            $table->string('account_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_banks');
    }
};
