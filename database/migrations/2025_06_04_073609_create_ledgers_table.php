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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('account')->index();
            $table->string('parent_account')->index();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->integer('account_type_id');
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('transaction_type');
            $table->string('transaction_id')->nullable();
            $table->boolean('is_journal')->default(0);
            $table->integer('journal_no')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
