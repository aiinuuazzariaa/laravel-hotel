<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    //read data start
    public function show()
    {
        $dt_user = user::get();
        return Response()->json($dt_user);
    }

    public function detail($id)
    {
        if (user::where('id_user', $id)->exists()) {
            // $data = DB::table('user')
            // ->where('id_user', '=', $id)
            // ->select('user.*')
            // ->get();

            // return Response()->json($data);

            $data = user::where('id_user', $id)->first();
            return Response()->json($data);
        } else {
            return Response()->json(['message' => 'Data not found']);
        }
    }
    //read data end

    //register user start
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'image' => 'required|image|mimes:jpeg,jpg,png',
            'email' => 'required|email|unique:user',
            'password' => 'required|min:8|confirmed',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson());
        }

        $photoName = time() . '.' . request()->image->getClientOriginalExtension();
        request()->image->move(public_path('user_image'), $photoName);

        $save = user::create([
            'user_name' => $request->get('user_name'),
            'image' => $photoName,
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'role' => $request->get('role'),
        ]);

        if ($save) {
            $data = User::where('email', '=', $request->email)
                ->get();
            return Response()->json(['status' => true, 'message' => 'User data successfully added !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'User data failed to add !']);
        }
    }
    //register user end

    //update data start
    public function update($id, Request $req)
    {
        $validator = Validator::make(
            $req->all(),
            [
                'name_user' => 'required',
                'email' => "required|email|unique:user,email,$id,id_user",
                'password' => 'required|min:8|regex:/^.(?=.{3,})(?=.[a-zA-Z])(?=.[false-9])(?=.[\d\x])(?=.[@!$#%]).$/|confirmed',
                'role' => 'required'
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors()->toJson());
        }

        $update = user::where('id_user', $id)
            ->update([
                'name_user' => $req->get('name_user'),
                'email' => $req->get('email'),
                'password' => Hash::make($req->get('password')),
                'role' => $req->get('role')
            ]);

        if ($update) {
            $data = user::where('id_user', '=', $id)
                ->get();
            return Response()->json(['status' => true, 'message' => 'User data successfully updated !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'User data failed to update !']);
        }
    }
    //update data end

    //upload image start
    public function uploadImage(Request $req, $id)
    {
        $validator = Validator::make(
            $req->all(),
            [
                'image' => 'required|image|mimes:jpeg,jpg,png'
            ]
        );

        if ($validator->fails()) {
            return Response()->json($validator->errors());
        }

        $photoName = time() . '.' . request()->image->getClientOriginalExtension();

        //proses upload image
        request()->image->move(public_path('user_image'), $photoName);

        $update = user::where('id_user', $id)
            ->update(
                [
                    'image' => $photoName
                ]
            );

        if ($update) {
            $data = user::where('id_user', '=', $id)
                ->get();
            return Response()->json(['status' => true, 'message' => 'Image successfully upload !', 'data' => $data]);
        } else {
            return Response()->json(['status' => false, 'message' => 'Failed upload photo !']);
        }
    }
    //upload data end

    //login user start
    public function login(Request $req)
    {
        $cred = $req->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($cred)) {
                return response()->json(['error' => 'Invalid credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        $dt = User::where('email', '=', $req->email)
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Login successfully !',
            'token' => $token,
            'data' => $dt
        ]);
    }
    //login user end

    //login check start
    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['User not found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['Token expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['Token invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['Token absent'], $e->getStatusCode());
        }
        //return response()->json(compact('user'));
        return response()->json([
            'status' => true,
            'message' => 'Success login !',
            'data' => $user
        ]);
    }
    //login check end

}
