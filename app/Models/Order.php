<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    public $primaryKey = 'id_order';
    public $timestamps = false;

    protected $fillable = [
        'order_number',
        'order_name',
        'customer_email',
        'order_date',
        'check_in_date',
        'check_out_date',
        'guest_name',
        'room_total',
        'id_room_type',
        'order_status',
        'id_user',
    ];

    public function class() {
        return $this->belongsTo('App\Models\room_type','id_room_type');
        return $this->belongsTo('App\Models\User','id_user');
    }
}
