<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('homepage_section_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->cascadeOnDelete();
            $table->string('path', 2048);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['homepage_section_id', 'sort_order']);
        });

        DB::table('homepage_sections')->whereNotNull('image_path')->orderBy('id')->each(function ($section) {
            DB::table('homepage_section_images')->insert([
                'homepage_section_id' => $section->id,
                'path' => $section->image_path,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('homepage_section_images');
    }
};
