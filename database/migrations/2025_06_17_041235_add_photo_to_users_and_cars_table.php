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
        Schema::table('users', function (Blueprint $table) {
            $table->string('path')->nullable()->after('role_id');
        });
        Schema::table('cars', function (Blueprint $table) {
            $table->string('path')->nullable()->after('model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('path');
        });
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('path');
        });
    }
};
