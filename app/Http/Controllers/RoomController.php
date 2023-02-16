<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    //read data start
    public function show(){
        return room::all();
    }

    public function detail($id){
        if(DB::table('rooms')->where('id_room', $id)->exists()){$detail_room = DB::table('rooms')->select('rooms.*')->where('id_room', $id)->first();
            return Response()->json($detail_room);
        }else {
            return Response()-> json(['message' => 'Couldnt Find The Data']);
        }
    }
    //read data end

    //create data start
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required',
            'id_room_type' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $store = room::create([
            'room_number' => $request -> room_number,
            'id_room_type' => $request -> id_room_type
        ]);

        $data = room::where('id_room', '=', $request->id_room)->get();
        if($store){
            return Response() -> json(['status' => 1,'message' => 'Room Data Successfully Added !','data' => $data]);
        } else {   
            return Response()->json(['status' => 0,'message' => 'Room Data Failed To Add !']);
        }
    }
    //create data end

    //update data start
    public function update($id, Request $request){
        $validator = Validator::make($request->all(),
        [
            'room_number' => 'required',
            'id_room_type' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $update = DB::table('rooms')->where('id_room', '=', $id)
        ->update([
            'room_number' => $request -> room_number,
            'id_room_type' => $request -> id_room_type
        ]);

        $data=room::where('id_room', '=', $id)->get();
        if($update){
            return Response() -> json(['status' => 1,'message' => 'Room Data Successfully Updated !','data' => $data  ]);
        } else {
            return Response() -> json(['status' => 0,'message' => 'Room Data Failed To Update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id){
        $delete = DB::table('rooms')->where('id_room', '=', $id)->delete();

        if($delete){
            return Response() -> json(['status' => 1,'message' => 'Room Data Successfully Deleted !']);
        } else {
            return Response() -> json(['status' => 0,'message' => 'Room Data Failed To Delete !']);
        }
    }
    //delete data end
}