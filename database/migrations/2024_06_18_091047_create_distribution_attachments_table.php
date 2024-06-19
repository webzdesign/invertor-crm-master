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
        Schema::create('distribution_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_id');
            $table->string('name');
            $table->boolean('type')->default(0)->comment("0 = Image | 1 PDF");
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('distribution_id')->references('id')->on('distributions');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->text('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_attachments');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
