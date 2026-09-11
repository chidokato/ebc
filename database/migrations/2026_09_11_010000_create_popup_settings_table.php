<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('offer_content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('offer_image_path')->nullable();
            $table->string('launch_label');
            $table->string('submit_label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_settings');
    }
};
