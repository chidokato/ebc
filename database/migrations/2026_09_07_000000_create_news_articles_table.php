<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('translation_group');
            $table->string('locale', 2);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('image_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['translation_group', 'locale']);
            $table->index(['locale', 'published_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_articles');
    }
};
