<?php

namespace App\Http\Controllers;

use App\Models\Room_Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    //read data start
    public function show()
    {
        return room_type::all();
    }

    public function detail($id)
    {
        if (DB::table('room_types')
            ->where('id_room_type', $id)
            ->exists()
        ) {
            $detail_room_type = DB::table('room_types')
                ->select('room_types.*')
                ->where('id_room_type', $id)->first();
            return Response()->json($detail_room_type);
        } else {
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    }
    //read data end

    //create data start
    public function upload_photo_hotel(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'photo' => 'required|file|mimes:jpeg,png,jpg',
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        //define nama file yg akan diupload
        $photoName = time() . '.' . $request->photo->getClientOriginalExtension();

        //proses upload
        request()->photo->move(public_path('room_type_image'), $photoName);

        $update = DB::table('room_types')->where('id_room_type', $id)
            ->update([
                'photo' => $photoName
            ]);

        $data = room_type::where('id_room_type', '=', $id)
            ->get();
        if ($update) {
            return Response()->json([
                'status' => true,
                'message' => 'Photo successfully upload !',
                'data' => $data
            ]);
        } else {
            return Response()->json([
                'status' => false,
                'message' => 'Failed upload photo !'
            ]);
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_name' => 'required|unique:room_types',
            'price' => 'required',
            'desc' => 'required'
        ]);

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $store = room_type::create([
            'room_type_name' => $request->room_type_name,
            'price' => $request->price,
            'desc' => $request->desc,
        ]);

        $data = room_type::where('room_type_name', '=', $request->room_type_name)
            ->get();
        if ($store) {
            return Response()->json(['status' => true, 'message' => 'Room type data successfully added !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room type data failed to add !']);
        }
    }
    //create data end

    //filter data start
    public function filter(Request $req)
    {
        $valid = Validator::make($req->all(), [
            'check_in_date' => 'required|date',
            'duration' => 'required|integer',
            'type' => 'integer'
        ]);

        if ($valid->fails()) {
            return response()->json($valid->errors());
        }

        $in = new Carbon($req->check_in_date); //di carbon parse karena check_in_date awalnya string, sedangkan untuk addDays harus menggunakan integer jadi harus di carbpn parse
        $dur = $req->duration; //lamanya menginap per-malam
        $out = $in->addDays($dur); //check_out otomatis menggunakan variabel $in, kemudian di addDays dengan $duration

        $from = date($req->check_in_date);
        $to = date($out);

        $avail = DB::table('rooms')
            ->select('room_types.*', DB::raw('count(rooms.id_room) as available'))
            ->leftJoin('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->leftJoin('order_details', function ($join) use ($from, $to) {
                $join->on('rooms.id_room', '=', 'order_details.id_room')
                    ->whereBetween('order_details.access_date', [$from, $to]);
            })
            ->where('order_details.access_date', '=', NULL)
            ->groupBy('room_types.id_room_type')
            ->get();

        // $room = DB::table('room_type')
        //                     ->select('room_types.room_type_name', 'rooms.id_room', 'rooms.room_number', 'detail_order.access_date')
        //                     ->leftJoin('rooms', 'room_types.id_room_type', 'rooms.id_room_type')
        //                     ->leftJoin('detail_order',  function($join) use($from, $to){
        //                         $join->on('rooms.id_room', '=', 'detail_order.id_room')
        //                         ->whereBetween('detail_order.access_date', [$from, $to]);
        //                     })
        //                     ->where('detail_order.access_date', '=', NULL)
        //                     ->where('rooms.id_room_type', '=', $req->type)
        //                     ->orderBy('rooms.id_room')
        //                     ->get();

        if ($avail) {
            return response()->json([
                'status' => true,
                'data' => $avail,
                // 'room' => $room
            ]);
        }
    }
    //filter data end

    //update data start
    public function update($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_type_name' => 'required',
                'price' => 'required',
                'desc' => 'required',
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $update = DB::table('room_types')->where('id_room_type', '=', $id)
            ->update([
                'room_type_name' => $request->room_type_name,
                'price' => $request->price,
                'desc' => $request->desc,
            ]);

        $data = room_type::where('id_room_type', '=', $id)
            ->get();
        if ($update) {
            return Response()->json(['status' => true, 'message' => 'Room type data successfully updated !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room type data failed to update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id)
    {
        $delete = DB::table('room_types')->where('id_room_type', '=', $id)
            ->delete();
        if ($delete) {
            return Response()->json(['status' => true, 'message' => 'Room type data successfully deleted !']);
        } else {
            return Response()->json(['status' => false, 'message' => 'Room type data failed to delete !']);
        }
    }
    //delete data end
}
