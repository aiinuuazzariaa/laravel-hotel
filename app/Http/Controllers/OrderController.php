<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //read data start
    public function show(){
        return order::all();
    }

    public function detail($id){
        if(DB::table('orders')->where('id_order', $id)->exists()){$detail_order = DB::table('orders')->select('orders.*')->where('id_order', $id)->first();
            return Response()->json($detail_order);
        }else {
            return Response()-> json(['message' => 'Couldnt Find The Data']);
        }
    }
    //read data end

    //create data start
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required',
            'order_name' => 'required',
            'customer_email' => 'required',
            'order_date' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required',
            'guest_name' => 'required',
            'room_total' => 'required',
            'id_room_type' => 'required',
            'order_status' => 'required',
            'id_user' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $store = order::create([
            'order_number' => $request -> order_number,
            'order_name' => $request -> order_name,
            'customer_email' => $request -> customer_email,
            'order_date' => $request -> order_date,
            'check_in_date' => $request -> check_in_date,
            'check_out_date' => $request -> check_out_date,
            'guest_name' => $request -> guest_name,
            'room_total' => $request -> room_total,
            'id_room_type' => $request -> id_room_type,
            'order_status' => $request -> order_status,
            'id_user' => $request -> id_user
        ]);

        $data = order::where('id_order', '=', $request->id_order)->get();
        if($store){
            return Response() -> json(['status' => 1,'message' => 'Order Data Successfully Added !','data' => $data]);
        } else {   
            return Response()->json(['status' => 0,'message' => 'Order Data Failed To Add !']);
        }
    }
    //create data end

    //update data start
    public function update($id, Request $request){
        $validator = Validator::make($request->all(),
        [
            'order_number' => 'required',
            'order_name' => 'required',
            'customer_email' => 'required',
            'order_date' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required',
            'guest_name' => 'required',
            'room_total' => 'required',
            'id_room_type' => 'required',
            'order_status' => 'required',
            'id_user' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $update = DB::table('orders')->where('id_order', '=', $id)
        ->update([
            'order_number' => $request -> order_number,
            'order_name' => $request -> order_name,
            'customer_email' => $request -> customer_email,
            'order_date' => $request -> order_date,
            'check_in_date' => $request -> check_in_date,
            'check_out_date' => $request -> check_out_date,
            'guest_name' => $request -> guest_name,
            'room_total' => $request -> room_total,
            'id_room_type' => $request -> id_room_type,
            'order_status' => $request -> order_status,
            'id_user' => $request -> id_user
        ]);

        $data=order::where('id_order', '=', $id)->get();
        if($update){
            return Response() -> json(['status' => 1,'message' => 'Order Data Successfully Updated !','data' => $data  ]);
        } else {
            return Response() -> json(['status' => 0,'message' => 'Order Data Failed To Update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id){
        $delete = DB::table('orders')->where('id_order', '=', $id)->delete();

        if($delete){
            return Response() -> json(['status' => 1,'message' => 'Order Data Successfully Deleted !']);
        } else {
            return Response() -> json(['status' => 0,'message' => 'Order Data Failed To Delete !']);
        }
    }
    //delete data end
}