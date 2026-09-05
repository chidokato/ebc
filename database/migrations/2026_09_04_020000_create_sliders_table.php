<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->string('image_path');
            $table->string('button_label', 100)->nullable();
            $table->string('button_url', 2048)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['locale', 'sort_order', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sliders');
    }
};
