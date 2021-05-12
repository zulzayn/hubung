<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserTemporary;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     // 'name' => 'required|string|max:255',
        //     // 'email' => 'required|string|email|max:255|unique:users',
        //     // 'password' => 'required|string|confirmed|min:8',
        // ]);

        $validator = Validator::make($request->all(), [
            'nick_name' => 'required|string|max:255',
            'phone_number' => 'required|unique:users|string|max:64',
        ],[
            'phone_number.unique' => 'Phone number already registered. Please login.'
        ]);
        

         if($validator->fails()){
            $data = [
                'status' => 'error', 
                'type' => 'Validation Error',
                'message' => 'Validation error, please check back your input.' ,
                'error_list' => $validator->messages() ,
            ];
            return json_encode($data);
         }
     
        $otp = rand(1000,9999);
        $otp_expired = date("Y-m-d h:i:s", time() + 300);
        
        $user_temporary = UserTemporary::create([
            'nick_name' => $request->nick_name,
            'phone_number' => $request->phone_number,
            'otp' => $otp,
            'otp_expired' => $otp_expired,
        ]);

        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        // ]);

        // event(new Registered($user));

        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);

        $data = [
            'status' => '200', 
            'message' => 'Successfully request OTP' ,
            'user_id' => $user_temporary->id,
        ];
        return json_encode($data);
    }


}
