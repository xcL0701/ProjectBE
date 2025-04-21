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
        Schema::create('orders', function (Blueprint $table) {
            $table->char('id')->primary(); // UUID string
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('shipping_method', ['pickup', 'delivery']);
            $table->bigInteger('total_price')->default(0);
            $table->bigInteger('total_paid')->default(0);
            $table->bigInteger('shipping_cost')->default(0);
            $table->bigInteger('initial_payment')->nullable();
            $table->enum('status', ['pending', 'paid', 'installment', 'canceled'])->default('pending');
            $table->text('address')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
