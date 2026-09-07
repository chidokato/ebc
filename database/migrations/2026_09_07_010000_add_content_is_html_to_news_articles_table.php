<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->boolean('content_is_html')->default(false);
        });
    }

    public function down()
    {
        Schema::table('news_articles', fn (Blueprint $table) => $table->dropColumn('content_is_html'));
    }
};
