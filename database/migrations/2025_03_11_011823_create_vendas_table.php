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
        Schema::create('vendas', function (Blueprint $table) {
            $table->id('id_venda');
            $table->unsignedBigInteger('id_carro');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_funcionario');
            $table->date('data_venda');
            $table->decimal('valor_final', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_carro')
                ->references('id_carro')->on('carros')
                ->onDelete('cascade');
            $table->foreign('id_cliente')
                ->references('id_cliente')->on('clientes')
                ->onDelete('cascade');
            $table->foreign('id_funcionario')
                ->references('id_funcionario')->on('funcionarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
