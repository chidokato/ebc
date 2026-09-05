<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->string('icon_path', 2048)->nullable()->after('image_path');
        });
    }

    public function down()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropColumn('icon_path');
        });
    }
};
