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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('email')->index();
            $table->string('contact')->index();
            $table->string('address')->nullable();
            $table->string('tax_pin',15)->nullable()->index();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
