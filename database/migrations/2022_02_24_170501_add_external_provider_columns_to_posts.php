<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalProviderColumnsToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('external_service_provider_id')->nullable()->constrained()->onDelete('set null')->onUpdate('set null');
            $table->string('external_service_provider_order_id')->nullable();
            $table->string('external_service_provider_payment_method')->nullable();
            $table->string('external_service_provider_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['external_service_provider_id']);
            $table->dropColumn('external_service_provider_id');
            $table->dropColumn('external_service_provider_order_id');
            $table->dropColumn('external_service_provider_payment_method');
            $table->dropColumn('external_service_provider_notes');
        });
    }
}
