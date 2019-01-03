<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_packets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('userid')->comment('用户ID');
            $table->decimal('issus_sum',18,4)->nullable()->comment('发送总额');
            $table->integer('tail_number')->nullable()->comment('尾号');
            $table->integer('count')->nullable()->default(10)->comment('红包总个数');
            $table->string('eosid',255)->nullable()->comment('区块链id');
            $table->string('blocknumber',255)->nullable()->comment('blocknumber');
            $table->string('addr',255)->nullable()->comment();
            $table->integer('status')->nullable()->default(1)->comment('状态(1-未领完，2-已领完，3-退回，4-冻结)');
            $table->decimal('surplus_sum',18,4)->nullable()->comment('红包抢完后剩余的金额');
            $table->integer('is_guangbo')->nullable()->default(0)->comment('用于标志抢完红包后是否广播,0-未广播，1-已广播');
//            $table->string('coin_type',10)->nullable()->default('EOS')->comment('用于区分币的种类-EOS,OWN,EUSD');
            // 创建时间为 发送时间， 更新时间为 领完时间/退回时间
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
        Schema::dropIfExists('out_packets');
    }
}
