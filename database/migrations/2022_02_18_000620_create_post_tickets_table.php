<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('RESTRICT')->onUpdate('RESTRICT');
            $table->foreignId('ticket_type_id')->nullable()->constrained()->onDelete('RESTRICT')->onUpdate('RESTRICT');
            $table->string('code');
            $table->boolean('status')->nullable();
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
        Schema::dropIfExists('post_tickets');
    }
}
