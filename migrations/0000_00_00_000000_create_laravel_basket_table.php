<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaravelBasketTable extends Migration
{
    public function up()
    {
        Schema::create('basket_storage', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('key')->index();
            $table->string('ip_address');
            $table->text('payload');
            $table->string('expiry');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('basket_storage');
    }
}
