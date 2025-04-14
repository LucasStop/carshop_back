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
        Schema::table('employees', function (Blueprint $table) {
            Schema::dropIfExists('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('cpf', 14)->unique()->nullable(); // Ex.: 000.000.000-00
            $table->string('rg', 20)->unique()->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address', 200)->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('position', 50)->nullable();
            $table->date('hire_date')->nullable();
            $table->string('salary', 20)->nullable();
            $table->timestamps();
        });
    }
};
