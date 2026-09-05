<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->index();
            $table->string('label', 100);
            $table->string('url', 2048);
            $table->enum('location', ['header', 'footer'])->default('header');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['locale', 'location', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
