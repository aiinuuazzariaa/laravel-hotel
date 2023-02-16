<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->integer('order_number');
            $table->string('order_name');
            $table->string('customer_email')->unique();
            $table->timestamp('order_date');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('guest_name');
            $table->integer('room_total');
            $table->unsignedBigInteger('id_room_type');
            $table->enum('order_status',['new','check_in','check_out']);
            $table->unsignedBigInteger('id_user');

            $table->foreign('id_room_type')->references('id_room_type')->on('room_types');
            $table->foreign('id_user')->references('id_user')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
