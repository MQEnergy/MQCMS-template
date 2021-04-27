<?php

declare (strict_types=1);
namespace App\Model\Common;

/**
 * @property int $id 
 * @property string $uuid uuid
 * @property string $fingerprint 设备唯一识别码
 * @property string $type 类型 mt,ele,jd,jdjk,jddj
 * @property string $imei 设备imei
 * @property string $data 原始数据
 * @property int $year 上传数据的年
 * @property int $month 上传数据的月
 * @property int $day 上传数据的日
 * @property \Carbon\Carbon $created_at 
 */
class SpiderSource extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spider_source';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uuid', 'fingerprint', 'type', 'imei', 'data', 'year', 'month', 'day', 'created_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'year' => 'integer', 'month' => 'integer', 'day' => 'integer', 'created_at' => 'datetime'];
}