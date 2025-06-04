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
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->string('description')->nullable()->after('account_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
