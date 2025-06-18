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
        Schema::create('product_category_filters', function (Blueprint $table) {
            $table->id();
             $table->foreignId('product_id')->constrained()->cascadeOnDelete();
             $table->foreignId('category_filter_id')->constrained()->cascadeOnDelete();
             $table->string('category_filter_option_id')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_filters');
    }
};
