<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    use SoftDeletes;

    public function up(): void
    {
        Schema::create('carros', function (Blueprint $table) {
            $table->id('id_carro');
            $table->unsignedBigInteger('id_modelo');
            $table->string('vin', 50)->unique();
            $table->string('cor', 30)->nullable();
            $table->integer('ano_fabricacao')->nullable();
            $table->integer('quilometragem')->nullable();
            $table->enum('status', ['disponível', 'vendido', 'reservado', 'manutenção'])->default('disponível');
            $table->date('data_inclusao')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_modelo')
                  ->references('id_modelo')->on('modelos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carros');
    }
};
