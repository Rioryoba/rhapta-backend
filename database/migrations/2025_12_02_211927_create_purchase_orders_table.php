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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->text('items')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['open', 'ordered', 'received', 'cancelled'])->default('open');
            $table->date('order_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
