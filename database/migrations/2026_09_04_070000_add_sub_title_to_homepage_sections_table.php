<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->string('sub_title', 255)->nullable()->after('title');
        });
    }

    public function down()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropColumn('sub_title');
        });
    }
};
