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
        Schema::create('invoice_headers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('invoice_date');
            $table->string('invoice_no')->index();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('restrict');
            $table->enum('terms',['0','30','60'])->default('0');
            $table->date('due_date')->nullable();
            $table->enum('vat_type',['no_vat','exclusive','inclusive'])->default('no_vat');
            $table->decimal('vat', 5, 2)->default(0.00);
            $table->decimal('discount',10,2)->default(0);
            $table->decimal('sub_total')->default(0.00);
            $table->decimal('vat_amount')->default(0.00);
            $table->decimal('total_amount')->default(0.00);
            $table->foreignUuid('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_headers');
    }
};
