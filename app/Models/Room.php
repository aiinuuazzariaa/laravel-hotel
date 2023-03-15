<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';
    public $primaryKey = 'id_room';
    public $timestamps = false;

    protected $fillable = [
        'room_number',
        'id_room_type',
    ];

    public function class()
    {
        return $this->belongsTo('App\Models\room_type', 'id_room_type');
    }
}
