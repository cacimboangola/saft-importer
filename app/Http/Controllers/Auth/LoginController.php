<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function requestToken(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('email', $request->email)->where('status', 1)->first();
        if (!$user) {
          return response()->json(["msg" => "Utilizador não encontrado ou inativo"], 403);
        }

        if(!$user->phone_number_is_verified){
          return response()->json(["msg" => "Conta não Verificada! Vai em criar conta e verifique sua conta"], 403);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([], 403);
        }
        $response = Http::get("https://cacimboweb.com/api/users-with-roles/".$user->id);
        $roles = json_decode($response, true);
        $token = $user->createToken($request->device_name)->plainTextToken;
        $user->getLastCompanyUsed();
      	$empresas = [];
		if(isset($request->package) && $request->package == "com.cacimbo.gesthotel"){
          $user->getLastCompanyUsedGestHotel();
          $empresas = $user->getEmpresasGestHotel();
        }else{
          $empresas = $user->getEmpresas();
        }
        return response()->json(['token'=> $token, 'user'=> $user, 'empresas'=>$empresas,"roles"=> $roles], 200);
    }

    public function getEmpresasByLoggedUserId(User $user, Request $request)
    {
      $empresas = [];
		if(isset($request->package) && $request->package == "com.cacimbo.gesthotel"){
          $user->getLastCompanyUsedGestHotel();
          $empresas = $user->getEmpresasGestHotel();
        }else{
          $user->getLastCompanyUsed();
          $empresas = $user->getEmpresas();
        }
        
        return response()->json(['user'=> $user, 'empresas'=>$empresas], 200);
    } 

}
