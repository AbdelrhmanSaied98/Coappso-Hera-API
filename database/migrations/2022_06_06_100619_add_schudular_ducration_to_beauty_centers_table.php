<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchudularDucrationToBeautyCentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_centers', function (Blueprint $table) {
            $table->integer('scheduler_duration')->nullable();
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
            $table->dropColumn('scheduler_duration');
        });
    }
}
