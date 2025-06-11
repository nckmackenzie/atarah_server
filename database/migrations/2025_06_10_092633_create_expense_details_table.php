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
        Schema::create('expense_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('gl_account_id')->constrained('gl_accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_details');
    }
};
