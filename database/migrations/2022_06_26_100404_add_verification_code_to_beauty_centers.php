<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationCodeToBeautyCenters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_centers', function (Blueprint $table) {
            $table->integer('verification_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beauty_centers', function (Blueprint $table) {
            $table->dropColumn('verification_code');
        });
    }
}
