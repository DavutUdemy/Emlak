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
    //Bu bizim login methodumuz
    public function authenticate(Request $request)
    {
        //Login icin sadece email ve parola gerektigini belirtiyoruz
        $credentials = $request->only('email', 'password');

        //Try Catch blokunun icerisinde kosularimizi yaziyoruz
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                //Eger gecersiz bi token ise o zaman invalid_credentials yaziyorz
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            //Eger bi hata olusursa o zaman 500 erro basiyor
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        //Her sey den basarili bi sekilde gecerse o zaman token generate ediliyor

        //Son olarak usera gosteriliyor
        return response()->json(compact('token'));
    }


    //Burasi bizim kullanici olusturma kismimiz
    public function register(Request $request)
    {
        //Burda Validator ile Validation kuralarimizi yazdik
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        //Burdada User Olusturuyoruz ve veritabanina kaydediyoruz
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        //User icin token generate ediyoruz
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }
}
