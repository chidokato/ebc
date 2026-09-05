<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->string('translation_group', 64)->nullable()->after('parent_id')->index();
        });

        $sections = DB::table('homepage_sections')->select('id', 'parent_id', 'key')->orderBy('id')->get();
        $keysById = $sections->pluck('key', 'id');

        foreach ($sections as $section) {
            $parentKey = $section->parent_id ? $keysById->get($section->parent_id) : 'root';
            $group = 'existing:' . $parentKey . ':' . ($section->key ?: $section->id);
            DB::table('homepage_sections')->where('id', $section->id)->update(['translation_group' => $group]);
        }
    }

    public function down()
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropIndex(['translation_group']);
            $table->dropColumn('translation_group');
        });
    }
};
