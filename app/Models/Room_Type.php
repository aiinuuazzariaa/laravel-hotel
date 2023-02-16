<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room_Type extends Model
{
    use HasFactory;
    protected $table = 'room_types';
    public $primaryKey = 'id_room_type';
    public $timestamps = false;

    protected $fillable = [
        'room_type_name',
        'price',
        'desc',
        'photo',
    ];
}
