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
        Schema::table('models', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->after('base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
