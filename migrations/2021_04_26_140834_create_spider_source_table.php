<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSpiderSourceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spider_source', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 32)->nullable(false)->comment('uuid');
            $table->string('keyword')->nullable(false)->comment('关键词');
            $table->string('fingerprint', 255)->nullable(false)->comment('设备唯一识别码');
            $table->string('type', 6)->nullable(false)->comment('类型 mt,ele,jd,jdjk,jddj');
            $table->string('imei', 64)->nullable(false)->comment('设备imei');
            $table->json('data')->nullable(false)->comment('原始数据');
            $table->integer('year')->nullable(false)->comment('上传数据的年');
            $table->integer('month')->nullable(false)->comment('上传数据的月');
            $table->integer('day')->nullable(false)->comment('上传数据的日');
            $table->unsignedBigInteger('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spider_source');
    }
}
