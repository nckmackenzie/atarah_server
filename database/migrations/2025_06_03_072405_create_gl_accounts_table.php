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
        Schema::create('gl_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->integer('account_type_id');
            $table->integer('parent_id')->nullable();
            $table->boolean('is_subcategory')->default(true);
            $table->boolean('is_bank')->default(false);
            $table->string('account_no')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_editable')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gl_accounts');
    }
};
