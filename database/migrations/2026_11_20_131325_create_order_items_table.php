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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_type', 32);
            $table->decimal('unit_price', 12, 2);
            $table->char('currency', 3);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('download_path')->nullable();
            $table->uuid('download_token')->nullable()->unique();
            $table->unsignedSmallInteger('download_count')->default(0);
            $table->unsignedSmallInteger('download_limit')->default(3);
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
