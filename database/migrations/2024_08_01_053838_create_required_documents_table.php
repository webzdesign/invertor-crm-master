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
        Schema::create('required_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('name');
            $table->text('description');
            $table->integer('sequence');
            $table->boolean('allow_only_specific_file_format')->default(false);
            $table->string('allowed_file')->comment("1 = Document | 2 = Presentation | 3 = Spreadsheet | 4 = Drawing | 5 = PDF | 6 = Image | 7 = Video | 8 = Audio")->nullable();
            $table->tinyInteger('maximum_upload_count')->default(1);
            $table->bigInteger('maximum_upload_size')->default(10485760)->comment('Size in Bytes');
            $table->boolean('is_required')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_documents');
    }
};
