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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('header_id')->constrained('invoice_headers')->onDelete('cascade');
            $table->foreignUuid('service_id')->constrained('services')->onDelete('restrict');
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('rate', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
