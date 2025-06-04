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
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('gift_title')->nullable()->default(null);
            $table->string('gift_images')->nullable()->default(null);
            $table->integer('status')->default(1);
            $table->integer('added_by')->nullable()->default(null);
            $table->integer('updated_by')->nullable()->default(null);
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
