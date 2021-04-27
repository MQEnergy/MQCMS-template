<?php

declare (strict_types=1);
namespace App\Model\Common;

/**
 * @property int $id 
 * @property string $uuid uuid
 * @property string $source_uuid 源文件记录的uuid
 * @property string $keyword 关键词
 * @property string $goods_name 商品名称
 * @property string $goods_price 商品价格
 * @property string $sale_num 商品销量
 * @property \Carbon\Carbon $created_at 
 */
class MtSource202104 extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mt_source_202104';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uuid', 'source_uuid', 'keyword', 'goods_name', 'goods_price', 'sale_num', 'created_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime'];
}