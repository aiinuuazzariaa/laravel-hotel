<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Facade\FlareClient\Http\Response;
use Facade\FlareClient\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function show() {
        $dt_user = Users::get();
        return Response()->json($dt_user);
    }

    public function register(Request $req) {
        $validator = Validator::make($req->all(),
        [
            'user_name'=>'required',
            'image' => 'required|image|mimes:jpeg,jpg,png',
            'email'=>'required|email|unique:user',
            // 'password'=>'required|confirmed',
            'password' => 'required|min:8|regex:/^.(?=.{3,})(?=.[a-zA-Z])(?=.[0-9])(?=.[\d\x])(?=.[@!$#%]).$/|confirmed',
            'role'=>'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('user_image'),$imageName);

        $save = Users::create([
            'user_name' => $req->get('user_name'),
            'image' => $imageName,
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
            'role' => $req->get('role')
        ]);

        if($save){
            $data = Users::where('email', $req->email)->get();
            return Response()->json(
                ['status' => true, 
                'message' => 'Succeed Add User',
                'data' => $data
            ]);
        }
        else {
            return Response()->json(['status' => false, 'message' => 'Failed Add User']);
        }
    }

    public function detail($id) {
        if(Users::where('user_id', $id)->exists()){
            // $data = DB::table('user')
            // ->where('user_id', '=', $id)
            // ->select('user.*')
            // ->get();

            // return Response()->json($data);

            $data = Users::where('user_id', $id)->first();
            return Response()->json($data);
        }
        else {
            return Response()->json(['message' => 'Data not found']);
        }
    }

    public function update($id, Request $req) {
        $validator = Validator::make($req->all(),
        [
            'user_name'=>'required',
            'email' => "required|email|unique:user,email,$id,user_id",
            // 'password'=>'required|confirmed',
            'password' => 'required|min:8|regex:/^.(?=.{3,})(?=.[a-zA-Z])(?=.[0-9])(?=.[\d\x])(?=.[@!$#%]).$/|confirmed',
            'role'=>'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $ubah = Users::where('user_id', $id)->update([
            'user_name' => $req->get('user_name'),
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
            'role' => $req->get('role')
        ]);

        if($ubah) {
            $data = Users::where('user_id', '=', $id)->get();
            return Response()->json([
                'status' => true, 
                'message' => 'Succeed update data',
                'data' => $data
            ]);
        }
        else {
            return Response()->json([
                'status' => false, 
                'message' => 'Failed update data'
            ]);
        }
    }

    public function uploadImage(Request $req, $id) {
        $validator = Validator::make($req->all(),
        [
            'image' => 'required|image|mimes:jpeg,jpg,png'
        ]);

        if($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('user_image'),$imageName);

        $ubah = Users::where('user_id',$id)->update(
            [
                'image' => $imageName
            ]);

        if($ubah) {
            $data = Users::where('user_id', '=', $id)->get();
            return Response()->json(
                [
                    'status' => true,
                    'message' => 'Succeed upload image',
                    'data' => $data
                ]);
        }
        else {
            return Response()->json(
                [
                    'status' => false,
                    'message' => 'Failed upload data'
                ]);
        }
    }

    public function destroy($id) {
        $hapus = Users::where('user_id', $id)->delete();
        if($hapus) {
            return Response()->json([
                'status' => true,
                'message' => 'Succeed delete data'
            ]);
        }
        else {
            return Response()->json([
                'status' => false,
                'message' => 'Failed delete data'
            ]);
        }
    }

    public function login(Request $req){
        $cred = $req->only('email', 'password');
        try{
            if(! $token = JWTAuth::attempt($cred)){
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        }
        catch(JWTException $e){
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $dt = Users::where('email', $req->email)->get();
        return response()->json([
            'status' => true,
            'message' => 'Login succeed',
            'token' => $token,
            'data' => $dt
        ]);
    }

    public function getAuthenticatedUser(){
        try{
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return response()->json(['user_not_found'], 404);
            }
        }
        catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return response()->json(['token_expired'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['token_invalid'], $e->getStatusCode());
        }
        catch(Tymon\JWTAuth\Exceptions\JWTException $e){
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    }
}