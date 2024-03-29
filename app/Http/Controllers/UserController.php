<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    public function login()
    {
        return view('users.login');
    }

    public function register()
    {
        return view('users.register');
    }
    public function loginCheck(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!empty($user)) {

            if (Hash::check($password, $user->password)) {

                Session::put('user_id', $user->id);

                if ($user->user_type == 'admin') {

                    return redirect()->route('admin.dash');
                } else {
                    return redirect()->route('user.home');
                }
            } else {
                return view('users.login', [
                    'msg' => 'Invalid username and password.',
                ]);
            }
        } else {
            return view('users.login', [
                'msg' => 'User does not exist.',
            ]);
        }
    }
    public function logout()
    {
        Session::flush();
        return redirect()->route('user.login');
    }
    public function UserStore(Request $request)
    {

        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5',
            'repeat_password' => 'same:password',
            'gender' => 'required',
            'phone' => 'required|min:10|max:10',
            'address' => 'required',
            'dob' => 'required',
        ]);

        $user = new User();
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone');
        $user->gender = $request->input('gender');
        $user->dob = $request->input('dob');
        $user->address = $request->input('address');
        if ($request->hasfile('image')) {
            $image = $request->file('image');
            $extention = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $extention;
            $image->move('template/img/', $imageName);
            $user->image = $imageName;
        }
        $user->save();


        return view('users.login');
    }
    public function home()
    {
        $userId = Session::get('user_id');

        $user = User::find($userId);
        $posts = Post::where('user_id', $userId)->get();
        return view('users.dashboard', ['user' => $user], compact('posts'));
    }
    public function profile()
    {
        $userId = Session::get('user_id');

        $user = User::find($userId);
        return view('users.profile', ['user' => $user]);
    }
    public function update(Request $request, $id)
    {

        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required|min:5',
            'repeat_password' => 'same:password',
            'gender' => 'required',
            'phone' => 'required|min:10|max:10',
            'address' => 'required',
            'dob' => 'required',
        ]);
        $userId = Session::get('user_id');
        $user = User::find($userId);

        $user = User::find($id);
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone');
        $user->gender = $request->input('gender');
        $user->dob = $request->input('dob');
        $user->address = $request->input('address');
        if ($request->hasFile('image')) {
            $destination = 'template/img/' . $user->image;
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $image = $request->file('image');
            $extention = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $extention;
            $image->move('template/img/', $imageName);
            $user->image = $imageName;
        }

        $user->save();


        return redirect()->route('user.home')->with('success', 'Profile Updated succesfully.');
    }
}