<?php

namespace App\Http\Controllers;

use App\Models\Room_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    //read data start
    public function show(){
        return room_type::all();
    }

    public function detail($id){
        if(DB::table('room_types')->where('id_room_type', $id)->exists()){$detail_room_type = DB::table('room_types')->select('room_types.*')->where('id_room_type', $id)->first();
            return Response()->json($detail_room_type);
        }else {
            return Response()-> json(['message' => 'Couldnt Find The Data']);
        }
    }
    //read data end

    //create data start
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_name' => 'required',
            'price' => 'required',
            'desc' => 'required',
            'photo' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $store = room_type::create([
            'room_type_name' => $request -> room_type_name,
            'price' => $request -> price,
            'desc' => $request -> desc,
            'photo' => $request -> photo
        ]);

        $data = room_type::where('id_room_type', '=', $request->id_room_type)->get();
        if($store){
            return Response() -> json(['status' => 1,'message' => 'Room Type Data Successfully Added !','data' => $data]);
        } else {   
            return Response()->json(['status' => 0,'message' => 'Room Type Data Failed To Add !']);
        }
    }
    //create data end

    //update data start
    public function update($id, Request $request){
        $validator = Validator::make($request->all(),
        [
            'room_type_name' => 'required',
            'price' => 'required',
            'desc' => 'required',
            'photo' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $update = DB::table('room_types')->where('id_room_type', '=', $id)
        ->update([
            'room_type_name' => $request -> room_type_name,
            'price' => $request -> price,
            'desc' => $request -> desc,
            'photo' => $request -> photo
        ]);

        $data=room_type::where('id_room_type', '=', $id)->get();
        if($update){
            return Response() -> json(['status' => 1,'message' => 'Room Type Data Successfully Updated !','data' => $data  ]);
        } else {
            return Response() -> json(['status' => 0,'message' => 'Room Type Data Failed To Update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id){
        $delete = DB::table('room_types')->where('id_room_type', '=', $id)->delete();

        if($delete){
            return Response() -> json(['status' => 1,'message' => 'Room Type Data Successfully Deleted !']);
        } else {
            return Response() -> json(  ['status' => 0,'message' => 'Room Type Data Failed To Delete !']);
        }
    }
    //delete data end
}