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
        Schema::create('call_task_statuses', function (Blueprint $table) {
             $table->id();
            $table->string('name');
            $table->string('slug');
            $table->integer('sequence')->nullable();
            $table->string('color', 12)->nullable();
            $table->boolean('type')->default(1)->comment("0 = System defined\n1 = User defined");
            $table->boolean('is_static')->default(0)->comment("0 = Non Static\n1 = Static");
            $table->boolean('status')->default(1)->comment("0 = InActive\n1 = Active");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_task_statuses');
    }
};
