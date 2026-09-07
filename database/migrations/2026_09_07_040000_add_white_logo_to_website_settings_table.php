<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->string('white_logo_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('website_settings', function (Blueprint $table) {
            $table->dropColumn('white_logo_path');
        });
    }
};
