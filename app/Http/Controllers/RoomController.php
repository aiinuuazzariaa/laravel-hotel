<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    //read data start
    public function show()
    {
        $room = DB::table('rooms')
            ->select('rooms.*', 'room_types.room_type_name')
            ->join('room_types', 'room_types.id_room_type', '=', 'rooms.id_room_type')
            ->get();
        return Response()->json($room);
    }

    public function detail($id)
    {
        if (DB::table('rooms')->where('id_room', $id)
            ->exists()
        ) {
            $detail_room = DB::table('rooms')
                ->select('rooms.*')
                ->where('id_room', $id)->first();
            return Response()->json($detail_room);
        } else {
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    }
    //read data end

    //create data start
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|unique:rooms',
            'id_room_type' => 'required'
        ]);

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $store = room::create([
            'room_number' => $request->room_number,
            'id_room_type' => $request->id_room_type
        ]);

        $data = room::where('room_number', '=', $request->room_number)
            ->get();
        if ($store) {
            return Response()->json(['status' => true, 'message' => 'Room data successfully added !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room data failed to add !']);
        }
    }
    //create data end

    //update data start
    public function update($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_number' => 'required',
                'id_room_type' => 'required'
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $update = DB::table('rooms')->where('id_room', '=', $id)
            ->update([
                'room_number' => $request->room_number,
                'id_room_type' => $request->id_room_type
            ]);

        $data = room::where('id_room', '=', $id)
            ->get();
        if ($update) {
            return Response()->json(['status' => true, 'message' => 'Room data successfully updated !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room data failed to update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id)
    {
        $delete = DB::table('rooms')->where('id_room', '=', $id)
            ->delete();
        if ($delete) {
            return Response()->json(['status' => true, 'message' => 'Room data successfully deleted !']);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room data failed to delete !']);
        }
    }
    //delete data end
}
