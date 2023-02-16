<?php
namespace App\Http\Controllers;

use App\Models\user;
use Facade\FlareClient\Http\Response;
use Facade\FlareClient\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function show() {
        $dt_user = user::get();
        return Response()->json($dt_user);
    }

    public function register(Request $request)
        {

        $validator = Validator::make($request->all(), [
        'user_name' => 'required',
        'image' => 'required|image|mimes:jpeg,jpg,png',
        'email' => 'required|email|unique:user',
        // 'password' => 'required|min:8|regex:/^.(?=.{3,})(?=.[a-zA-Z])(?=.[0-9])(?=.[\d\x])(?=.[@!$#%]).$/|confirmed',
        'password' => 'required|min:8|confirmed',
        'role' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson());
        }

        $photoName = time().'.'.request()->image->getClientOriginalExtension();
            request()->image->move(public_path('user_image'),$photoName);

        $save = user::create([
        'user_name' => $request->get('user_name'),
        'image' => $photoName,
        'email' => $request->get('email'),
        'password' => Hash::make($request->get('password')),
        'role' => $request -> get('role'),
        ]);
        
        if($save){
            $data = user::where('email', $request->email)->get();
            return Response()->json([
                'status' => 1, 
                'message' => 'User Data Successfully Updated !',
                'data' => $data
            ]);
        }
        else {
            return Response()->json(['status' => false, 'message' => 'User Data Failed To Update !']);
        }
    }

    public function detail($id) {
        if(user::where('id_user', $id)->exists()){
            // $data = DB::table('user')
            // ->where('id_user', '=', $id)
            // ->select('user.*')
            // ->get();

            // return Response()->json($data);

            $data = user::where('id_user', $id)->first();
            return Response()->json($data);
        }
        else {
            return Response()->json(['message' => 'Data Not Found']);
        }
    }

    public function update($id, Request $req) {
        $validator = Validator::make($req->all(),
        [
            'name_user'=>'required',
            'email' => "required|email|unique:user,email,$id,id_user",
            // 'password'=>'required|confirmed',
            'password' => 'required|min:8|regex:/^.(?=.{3,})(?=.[a-zA-Z])(?=.[0-9])(?=.[\d\x])(?=.[@!$#%]).$/|confirmed',
            'role'=>'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $ubah = user::where('id_user', $id)->update([
            'name_user' => $req->get('name_user'),
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
            'role' => $req->get('role')
        ]);

        if($ubah) {
            $data = user::where('id_user', '=', $id)->get();
            return Response()->json([
                'status' => 1, 
                'message' => 'User Data Successfully Updated !',
                'data' => $data
            ]);
        }
        else {
            return Response()->json([
                'status' => false, 
                'message' => 'User Data Failed To Update !'
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

        $photoName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('user_image'),$photoName);

        $ubah = user::where('id_user',$id)->update(
            [
                'image' => $photoName
            ]);

        if($ubah) {
            $data = user::where('id_user', '=', $id)->get();
            return Response()->json(
                [
                    'status' => 1,
                    'message' => 'Image Successfully Updated !',
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

    public function login(Request $req){
        $cred = $req->only('email', 'password');
        try{
            if(! $token = JWTAuth::attempt($cred)){
                return response()->json(['error' => 'Invalid Credentials'], 400);
            }
        }
        catch(JWTException $e){
            return response()->json(['error' => 'Could Not Create Token'], 500);
        }
        $dt = user::where('email', '=', $req->email)->get();
        return response()->json([
            'status' => 1,
            'message' => 'Login Successfully !',
            'token' => $token,
            'data' => $dt
        ]);
    }

    public function getAuthenticatedUser(){
        try{
            if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['User Not Found'], 404);
            }
        }
        catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return response()->json(['Token Expired'], $e->getStatusCode());
        }
        catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['Token Invalid'], $e->getStatusCode());
        }
        catch (Tymon\JWTAuth\Exceptions\JWTException $e){
            return response()->json(['Token Absent'], $e->getStatusCode());
        }
            //return response()->json(compact('user'));
            return response()->json([
                'status' => 1,
                'message' => 'Success Login !',
                'data' => $user
            ]);
        }

}