<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMtSource202104Table extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mt_source_202104', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 32)->nullable(false)->comment('uuid');
            $table->string('source_uuid', 32)->nullable(false)->comment('源文件记录的uuid');
            $table->string('keyword')->nullable(false)->comment('关键词');
            $table->string('goods_name')->nullable(false)->comment('商品名称');
            $table->string('goods_price')->nullable(false)->comment('商品价格');
            $table->string('sale_num')->nullable(false)->comment('商品销量');
            $table->unsignedBigInteger('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt_source_202104');
    }
}
