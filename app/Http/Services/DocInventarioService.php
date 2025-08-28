<?php

namespace App\Http\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DocInventarioService
{
    public static function getInventoryValorizationByNif($nif, $request){
        if (isset($request->armazens) && is_array($request->armazens)) {
            $array = $request->armazens;
            $query = http_build_query($array,'armazens[]');
            $query = preg_replace('/\[\]\d+/', '[]', $query);
            if (isset($request->valorizacao)) {
                $query = "valorizacao=".$request->valorizacao."&".$query;
            }
        }

        if (isset($request->valorizacao)) {
            $query = "valorizacao=".$request->valorizacao;
            if (isset($request->armazens) && is_array($request->armazens)) {
                $array = $request->armazens;
                $queryArmazem = http_build_query($array,'armazens[]');
                $queryArmazem = preg_replace('/\[\]\d+/', '[]', $query);
                $query = $queryArmazem."&".$query;
            }
        }
        if(isset($request->destaque)){
            $query = "destaque=".$request->destaque;
            if (isset($request->valorizacao)) {
                $query = "valorizacao=".$request->valorizacao."&".$query;
            }
            if (isset($request->armazens) && is_array($request->armazens)) {
                $array = $request->armazens;
                $queryArmazem = http_build_query($array,'armazens[]');
                $queryArmazem = preg_replace('/\[\]\d+/', '[]', $query);
                $query = $queryArmazem."&".$query;
            }
        }
        
        $response = (isset($query)) ? Http::get("http://inventario.cacimboweb.com/api/empresas/".$nif."/products-valorization?".$query)
                                      :  Http::get("http://inventario.cacimboweb.com/api/empresas/".$nif."/products-valorization");
 
        return ($response['data']['0']['qtd_artigos']) ? $response['data'] : [];

    }
    public static function getInventoryValorizationLinesByNifOld($nif, $request){
        if (isset($request->armazens) && is_array($request->armazens)) {
            $array = $request->armazens;
            $query = http_build_query($array,'armazens[]');
            $query = preg_replace('/\[\]\d+/', '[]', $query);
            if (isset($request->valorizacao)) {
                $query = "valorizacao=".$request->valorizacao."&".$query;
            }
            if(isset($request->page)){
                $query =  $query . "&page=".$request->page;  
            }
            if(isset($request->modo)){
                $query =  $query . "&modo=".$request->modo;  
            }
        }


        if (isset($request->valorizacao)) {
            $query = "valorizacao=".$request->valorizacao;
            if (isset($request->armazens) && is_array($request->armazens)) {
                $array = $request->armazens;
                $queryArmazem = http_build_query($array,'armazens[]');
                $queryArmazem = preg_replace('/\[\]\d+/', '[]', $query);
                $query = $queryArmazem."&".$query;
            }
            if(isset($request->page)){
                $query =  $query . "&page=".$request->page;  
            }
            if(isset($request->modo)){
                $query =  $query . "&destaque=".$request->modo;  
            }
        }


        if(isset($request->page)){
            $query =  "page=".$request->page; 
            if (isset($request->valorizacao)) {
                $query = "valorizacao=".$request->valorizacao."&".$query;
            }
            if (isset($request->armazens) && is_array($request->armazens)) {
                $array = $request->armazens;
                $queryArmazem = http_build_query($array,'armazens[]');
                $queryArmazem = preg_replace('/\[\]\d+/', '[]', $query);
                $query = $queryArmazem."&".$query;
            }
            if(isset($request->modo)){
                $query =  $query . "&modo=".$request->modo;  
            }
        }

        if(isset($request->modo)){
            $query = "modo=".$request->modo;
            if (isset($request->valorizacao)) {
                $query = "valorizacao=".$request->valorizacao."&".$query;
            }
            if (isset($request->armazens) && is_array($request->armazens)) {
                $array = $request->armazens;
                $queryArmazem = http_build_query($array,'armazens[]');
                $queryArmazem = preg_replace('/\[\]\d+/', '[]', $query);
                $query = $queryArmazem."&".$query;
            }
            if(isset($request->page)){
                $query =  $query . "&page=".$request->page;  
            }

        }
        $response = (isset($query)) ? Http::get("http://inventario.cacimboweb.com/api/empresas/".$nif."/products-valorization/details?".$query)
                                      :  Http::get("http://inventario.cacimboweb.com/api/empresas/".$nif."/products-valorization/details");

        return json_decode($response->getBody(), true);

    }

    public static function getInventoryValorizationLinesByNif($nif, $request){
        $query = '';
    
        if (isset($request->armazens) && is_array($request->armazens)) {
            $query .= http_build_query($request->armazens, 'armazens[]');
        }
    
        if (isset($request->valorizacao)) {
            $query .= ($query ? '&' : '') . "valorizacao=".$request->valorizacao;
        }
    
        if(isset($request->page)){
            $query .= ($query ? '&' : '') . "page=".$request->page;
        }
    
        if(isset($request->modo)){
            $query .= ($query ? '&' : '') . "modo=".$request->modo;
        }
        if (isset($request->page_size)) {
            $query .= ($query ? '&' : '') . "page_size=".$request->page_size;
        }
    
        $response = Http::get("http://inventario.cacimboweb.com/api/empresas/".$nif."/products-valorization/details" . ($query ? '?' . $query : ''));
    
        return json_decode($response->getBody(), true);
    }
    
}