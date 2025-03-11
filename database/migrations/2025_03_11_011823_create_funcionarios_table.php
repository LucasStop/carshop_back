<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    use SoftDeletes;

    public function up(): void
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id('id_funcionario');
            $table->string('nome', 100);
            $table->string('cargo', 50)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('cpf', 14)->unique()->nullable();
            $table->string('rg', 20)->unique()->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco', 200)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento', 50)->nullable();
            $table->string('cidade', 50)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->date('data_admissao')->nullable();
            $table->decimal('salario', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
