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
        Schema::create('information_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->nullable()->default(null);
            $table->string('page_banner')->nullable()->default(null);
            $table->text('page_description')->nullable()->default(null);
            $table->string('slug')->nullable()->default(null);
            $table->integer('status')->default(1);
            $table->integer('added_by')->nullable()->default(null);
            $table->integer('updated_by')->nullable()->default(null);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('information_pages');
    }
};
