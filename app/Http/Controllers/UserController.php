<?php

namespace App\Http\Controllers;



use App\Http\Services\DocEmpresaService;
use App\Http\Services\UserService;
use App\Models\User;
use App\Models\UserEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $senha =  Str::random(8);
        $dados = $request->except('_token');
        $dados['name'] = $request->name;
        $dados['email'] = $request->email;
        $dados['parceiro_id'] = $request->parceiro_id;
        $dados['password'] = Hash::make($senha);
        $dados['tipo'] = $request->tipo;
        $dados['id_perfil'] = $request->id_perfil;
        $userSaved = User::create($dados);
        //$mail = new sendPassword($userSaved, $senha);
        //Mail::send($mail);
        if ($user) {
            return response()->json($user, 201);
        }else{
            return response()->json([], 404);
        }
        
    }

    public function sendPushToken(Request $request) {

        $response = Http::put("https://cacimboweb.com/api/send-push-token", $request->all());
        if ($response) {
           return response()->json($response, 200);
        }
        else {
            return response()->json([], 400);
        }

        
    }

    public function getEmpresas(User $user)
    {
       $empresas = UserService::getEmpresas($user);
       if ($empresas) {
            return response()->json($empresas, 200);
        }else{
            return response()->json([], 404);
        }
    }
    
}