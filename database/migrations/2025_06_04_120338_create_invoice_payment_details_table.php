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
        Schema::create('invoice_payment_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('header_id')->constrained('invoice_payment_headers')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->constrained('invoice_headers')->onDelete('restrict');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_details');
    }
};
