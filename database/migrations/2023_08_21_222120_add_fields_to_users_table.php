<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable();
            $table->string('user_name')->nullable();
            $table->string('phrase')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->boolean('newsletter_email')->default(true);
            $table->boolean('terms_and_conditions')->nullable();
            $table->string('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
            $table->dropColumn('user_name');
            $table->dropColumn('phrase');
            $table->dropColumn('birth_date');
            $table->dropColumn('gender');
            $table->dropColumn('state');
            $table->dropColumn('city');
            $table->dropColumn('newsletter_email');
            $table->dropColumn('terms_and_conditions');
            $table->dropColumn('image');
        });
    }
}
