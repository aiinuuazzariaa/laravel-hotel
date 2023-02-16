<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_Detail extends Model
{
    use HasFactory;
    protected $table = 'order_details';
    public $primaryKey = 'id_order_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_order',
        'id_room',
        'access_date',
        'price_detail',
    ];

    public function class() {
        return $this->belongsTo('App\Models\order','id_order');
        return $this->belongsTo('App\Models\room','id_room');
    }
}
