<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Order_Detail;
use App\Models\Room_Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //read data start
    public function show()
    {
        $order = DB::table('orders')
            ->select('orders.*', 'room_types.room_type_name')
            ->join('room_types', 'room_types.id_room_type', '=', 'orders.id_room_type')
            ->get();
        return Response()->json($order);
    }

    public function detail($id)
    {
        if (DB::table('orders')
            ->where('id_order', $id)
            ->exists()
        ) {
            $detail_order = DB::table('orders')
                ->select('orders.*')
                ->where('id_order', $id)->first();
            return Response()->json($detail_order);
        } else {
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    }
    //read data end

    //create data start
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'customer_email' => 'required|email',
            'check_in_date' => 'required|date',
            'duration' => 'required|integer',
            'guest_name' => 'required',
            'room_total' => 'required|integer',
            'id_room_type' => 'required',
        ]);

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        //var date
        $dur = $request->duration; //lamanya menginap per-malam
        $in = Carbon::parse($request->check_in_date); //di carbon parse karena check_in_date awalnya string, sedangkan untuk addDays harus menggunakan integer jadi harus di carbon parse
        $out = $in->addDays($dur); //check_out otomatis menggunakan variabel $in, kemudian di addDays dengan $duration
        $from = date($request->check_in_date);
        $to = date($out);

        //var banyak kamar yang tersedia pada tipe kamar yang dipilih
        $available = DB::table('rooms')
            ->select('room_types.*', DB::raw('count(rooms.id_room) as available'))
            ->leftJoin('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->leftJoin('order_details', function ($join) use ($from, $to) {
                $join->on('rooms.id_room', '=', 'order_details.id_room')
                    ->whereBetween('order_details.access_date', [$from, $to]);
            })
            ->where('order_details.access_date', '=', NULL)
            ->where('rooms.id_room_type', $request->id_room_type)
            ->groupBy('room_types.id_room_type')
            ->first();
        $availRoom = $available->available;

        //percabangan jika yang dipesan lebih banyak dari yang tersedia
        if ($request->room_total > $availRoom) {
            return response()->json([
                'status' => false,
                'message' => 'Not enough room for your order !'
            ]);
        }

        $latest = Order::orderBy('id_order', 'DESC')->first(); //desc digunakan untuk mengurutkan data berdasarkan tanggal order paling baru
        if (is_null($latest)) { //latest digunakan seumpama databasenya kosong id = 0, jika database ada data, maka data terakhir adalah id_order terbaru
            $id = 0;
        } else {
            $id = $latest->id_order;
        }

        //variabel price
        $roomType = Room_Type::where('id_room_type', '=', $request->id_room_type)
            ->first();

        $order = new order();
        $order->order_number = 'ORD-NMB-' . str_pad($id + 1, 8, "0", STR_PAD_LEFT); //order number diambil dari id_order terbaru +1, angka 8 digit,angkan sebelum id_order adalah 0
        $order->customer_name = $request->customer_name;
        $order->customer_email = $request->customer_email;
        $order->order_date = Carbon::now('Asia/Jakarta');
        $order->check_in_date = $request->check_in_date;
        $order->check_out_date = $out; //variabel $out yang dimana check_out nya otomatis
        $order->guest_name = $request->guest_name;
        $order->room_total = $request->room_total;
        $order->id_room_type = $request->id_room_type;
        $order->order_status = 1;
        $order->save();

        //variabel total
        $total = 0;

        for ($i = 0; $i < $request->room_total; $i++) {

            //select room
            $room = DB::table('rooms')
                ->select('rooms.id_room')
                ->leftJoin('room_types', 'room_types.id_room_type', 'rooms.id_room_type')
                ->leftJoin('order_details', function ($join) use ($from, $to) {
                    $join->on('rooms.id_room', '=', 'order_details.id_room')
                        ->whereBetween('order_details.access_date', [$from, $to]);
                })
                ->where('order_details.access_date', '=', NULL)
                ->where('rooms.id_room_type', '=', $request->id_room_type)
                ->orderBy('rooms.id_room')
                ->first();

            //reset variabel access date
            $masuk = new Carbon($request->check_in_date); //untuk detail_order dimana variabel $masuk digunakan
            for ($j = 0; $j < $request->duration; $j++) { //kita sudah mendapat id_room dari variabel $room, perulangan ini digunakan untuk membuat data detail_order per-malam sebanyak durasi yang di inputkan
                $detail = new Order_Detail();
                $detail->id_order = $order->id_order;
                $detail->id_room = $room->id_room;
                $detail->access_date = $masuk;
                $detail->price_detail = $roomType->price;
                $total += $roomType->price;
                $detail->save(); //karena kalau, addDays terlebih dahulu kemudian di save tanggal check_in tambah 1 hari semisal check_in tanggal 10 - 11, di detail jadi 11 - 12
                $masuk->addDays(1); //harus +1 untuk menambah hari agar setiap data detail harinya bertambah
            }
        }

        $updateTotal = Order::where('id_order', '=', $order->id_order)->update([
            'total' => $total //update total di database, karena awalnya null
        ]);

        if ($order) {
            $data = Order::select('orders.*', 'room_types.id_room_type', 'room_types.room_type_name') //orders*.
                ->join('room_types', 'room_types.id_room_type', '=', 'orders.id_room_type')
                ->where('id_order', $order->id_order)
                ->get();

            $data_detail = Order_Detail::select('order_details.*', 'room_types.room_type_name', 'rooms.room_number') //order_number
                ->join('rooms', 'order_details.id_room', '=', 'rooms.id_room')
                ->join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
                ->where('order_details.id_order', '=', $order->id_order)
                ->get();

            return Response()->json([
                'status' => true, 'message' => 'Order data successfully added !', 'data' => $data, 'detail' => $data_detail
            ]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Order data failed to add !']);
        }
    }
    //create data end

    //filter data start
    public function findByNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required',
            'customer_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        if (Order::where('order_number', '=', $request->order_number)->exists()) {
            $order = Order::where('order_number', '=', $request->order_number)->first();
            if ($request->email == $order->customer_email) {
                $data = Order::select('orders.*', 'room_types.id_room_type', 'room_types.room_type_name')
                    ->join('room_types', 'room_types.id_room_type', '=', 'orders.id_room_type')
                    ->where('orders.order_number', $request->order_number)
                    ->get();

                $data_detail = Order_Detail::select('order_details.*', 'room_types.room_type_name', 'rooms.room_number')
                    ->join('rooms', 'order_details.id_room', '=', 'rooms.id_room')
                    ->join('room_types', 'room.id_room_type', '=', 'room_types.id_room_type')
                    ->where('order_details.id_order', '=', $order->order_id)
                    ->get();

                return response()->json([
                    'status' => true,
                    'message' => 'Data found !',
                    'data' => $data,
                    'data_detail' => $data_detail
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email does not match, please check again !'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found'
            ]);
        }
    }
    //filter data end

    //update data start
    public function status($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'order_status' => 'required',
                'id_user' => 'required|integer'
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors()->toJson());
        }

        $update = DB::table('orders')->where('id_order', '=', $id)
            ->update([
                'order_status' => $request->order_status,
                'id_user' => $request->id_user
            ]);

        $data = order::where('id_order', '=', $id)
            ->get();
        if ($update) {
            return Response()->json(['status' => true, 'message' => 'Order data successfully updated !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Order data failed to update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id)
    {
        $delete = DB::table('orders')->where('id_order', '=', $id)
            ->delete();
        if ($delete) {
            return Response()->json(['status' => true, 'message' => 'Order data successfully deleted !']);
        } else {
            return Response()->json(['status' => false, 'message' => 'Order data failed to delete !']);
        }
    }
    //delete data end
}
