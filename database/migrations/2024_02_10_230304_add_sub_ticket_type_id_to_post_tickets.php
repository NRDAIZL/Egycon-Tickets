<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubTicketTypeIdToPostTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_tickets', function (Blueprint $table) {
            $table->foreignId('sub_ticket_type_id')->nullable()->constrained('sub_ticket_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_tickets', function (Blueprint $table) {
            //
        });
    }
}
