<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->index();
            $table->foreignId('parent_id')->nullable()->constrained('homepage_sections')->cascadeOnDelete();
            $table->string('key', 80)->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('image_path', 2048)->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['locale', 'parent_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('homepage_sections');
    }
};
