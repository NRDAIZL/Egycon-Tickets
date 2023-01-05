<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('theme_color')->nullable();
            $table->string('registration_form_background_color')->nullable();
            $table->string('registration_page_background_image')->nullable();
            $table->string('registration_page_header_image')->nullable();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_themes');
    }
}
