<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email|max:255',
            'password'   => 'required|string|min:8|confirmed',
        ]);
        User::create([
            "first_name"=>$request->input('first_name'),
            "last_name"=>$request->input('last_name'),
            "email"=>$request->input('email'),
            "password"=>$request->input('password'),
        ]);
        //after the registration login the user
        $token=$this->login($request);
        return response()->json([
            "message"=>"Registration Successfully Completed",
            "token"=>$token
        ]);
    }
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::whereEmail($request->email)->first();
        if(!$user){
            return response()->json(["message"=>"user does not exist"]);
        }
        else{
            if(Hash::check($request->password, $user->password)){
                $token=$user->createToken("auth_token")->plainTextToken;
                return response()->json(["token"=>$token]);
            }
            else{
                return response()->json(["message"=>"wrong password"]);

            }
        }

    }

    public function profile(){
        return auth()->user();
    }
    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json(["message"=>"logout successfuly"]);
    }
    public function updateProfile(Request $request){
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'profile_photo_path'  => 'nullable'
        ]);
        $userUpdate=User::find(auth()->user()->id);
        $userUpdate->update([
            "first_name"=>$request->first_name,
            "last_name"=>$request->last_name,
            "profile_photo_path"=> $request->profile_photo_path,
        ]);
        return response()->json(["message"=>"user updated successfuly"]);
    }
}
