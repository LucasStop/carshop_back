<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, adicionar a coluna price na tabela cars
        Schema::table('cars', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('mileage');
        });

        // Migrar os dados existentes: copiar base_price do modelo para o preço do carro
        DB::statement('
            UPDATE cars 
            SET price = (
                SELECT base_price 
                FROM models 
                WHERE models.id = cars.model_id
            )
        ');

        // Remover a coluna base_price da tabela models
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Adicionar novamente a coluna base_price na tabela models
        Schema::table('models', function (Blueprint $table) {
            $table->decimal('base_price', 10, 2)->nullable()->after('power');
        });

        // Migrar os dados de volta: copiar price do carro para base_price do modelo
        // Usando o preço médio dos carros para cada modelo
        DB::statement('
            UPDATE models 
            SET base_price = (
                SELECT AVG(price) 
                FROM cars 
                WHERE cars.model_id = models.id 
                AND cars.price IS NOT NULL
            )
        ');

        // Remover a coluna price da tabela cars
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
