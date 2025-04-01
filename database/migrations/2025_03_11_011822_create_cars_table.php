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
        Schema::create('cars', function (Blueprint $table) {
            $table->id('car_id');
            $table->unsignedBigInteger('model_id');
            $table->string('vin', 50)->unique();
            $table->string('color', 30)->nullable();
            $table->integer('manufacture_year')->nullable();
            $table->integer('mileage')->nullable();
            $table->enum('status', ['available', 'sold', 'reserved', 'maintenance'])->default('available');
            $table->date('inclusion_date')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('model_id')
                  ->references('model_id')->on('models')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
