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
        Schema::create('invoice_payment_headers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('payment_date');
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('restrict');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'mpesa', 'cheque', 'bank'])->default('cheque');
            $table->string('payment_reference')->index();            
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_headers');
    }
};
