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
        User::create($request->all());
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
            'password' => 'required|string',
        ]);
    
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $token = auth()->user()->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Login successful.',
            'user' => auth()->user(),
            'token' => $token,
        ]);

    }

    public function profile()
    {
        $user = auth()->user()->load([
            'posts.media',
            'posts.comments',      // Load comments for each post
            'posts.postLikes',         // Load likes for each post
        ]);

        return response()->json($user);
    }
    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json(["message"=>"logout successfuly"]);
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'profile_photo_path'  => 'nullable'
        ]);

        $user->update($request->only('first_name', 'last_name', 'email','profile_photo_path'));

        return response()->json(['message' => 'Profile updated successfully.', 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), auth()->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        auth()->user()->update(['password' => bcrypt($request->input('new_password'))]);
        $this->logout();
        return response()->json(['message' => 'Password changed successfully.']);
    }
    public function show(User $user)
    {
        return response()->json($user->load([
            'posts.media',
            'posts.comments',      // Load comments for each post
            'posts.postLikes',         // Load likes for each post
        ]));
    }
    public function index(){
        return User::all();
    }



    public function deleteUser(Request $request){
        if(Hash::check($request->password, auth()->user()->password))
        {   
            $user=User::find(auth()->user()->id);
            $user->delete();
            return response()->json(["message"=>"user deleted successfuly"]);
        }
        return response()->json(["message"=>"wrong password"]);
    }
}
