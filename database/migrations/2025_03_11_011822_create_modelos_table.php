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
        Schema::create('modelos', function (Blueprint $table) {
            $table->id('id_modelo');
            $table->unsignedBigInteger('id_marca');
            $table->string('nome', 50);
            $table->integer('ano_modelo')->nullable();
            $table->string('motor', 50)->nullable();
            $table->integer('potencia')->nullable();
            $table->decimal('preco_base', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_marca')
                ->references('id_marca')->on('marcas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
