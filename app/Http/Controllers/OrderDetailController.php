<?php

namespace App\Http\Controllers;

use App\Models\Order_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderDetailController extends Controller
{
    //read data start
    public function show()
    {
        return order_detail::all();
    }

    public function detail($id)
    {
        if (DB::table('order_details')
            ->where('id_order_detail', $id)
            ->exists()
        ) {
            $detail_order = DB::table('order_details')
                ->select('order_details.*')
                ->where('id_order_detail', $id)->first();
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
            'id_order' => 'required',
            'id_room' => 'required',
            'access_date' => 'required',
            'price_detail' => 'required'
        ]);

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $store = order_detail::create([
            'id_order' => $request->id_order,
            'id_room' => $request->id_room,
            'access_date' => $request->access_date,
            'price_detail' => $request->price_detail
        ]);

        $data = order_detail::where('id_order_detail', '=', $request->id_order)
            ->get();
        if ($store) {
            return Response()->json(['status' => 1, 'message' => 'Order detail data successfully added !', 'data' => $data]);
        } else {
            return Response()->json(['status' => 0, 'message' => 'Order detail data failed to add !']);
        }
    }
    //create data end

    //update data start
    public function update($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_order' => 'required',
                'id_room' => 'required',
                'access_date' => 'required',
                'price_detail' => 'required'
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $update = DB::table('order_details')->where('id_order_detail', '=', $id)
            ->update([
                'id_order' => $request->id_order,
                'id_room' => $request->id_room,
                'access_date' => $request->access_date,
                'price_detail' => $request->price_detail
            ]);

        $data = order_detail::where('id_order_detail', '=', $id)
            ->get();
        if ($update) {
            return Response()->json(['status' => 1, 'message' => 'Order detail data successfully updated !', 'data' => $data]);
        } else {
            return Response()->json(['status' => 0, 'message' => 'Order detail data failed to update !']);
        }
    }
    //update data end

    //delete data start
    public function delete($id)
    {
        $delete = DB::table('order_details')->where('id_order_detail', '=', $id)
            ->delete();

        if ($delete) {
            return Response()->json(['status' => 1, 'message' => 'Order detail data successfully deleted !']);
        } else {
            return Response()->json(['status' => 0, 'message' => 'Order detail data failed to delete !']);
        }
    }
    //delete data end
}
