<?php

namespace App\Http\Services;

use App\Core\Util;
use App\Models\DocEmpresa;
use App\Models\OnlineStore;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;

class OnlineStoreService
{

    public static function  getAllOnlineStores() {
        $stores = OnlineStore::with("company.onlinePaymentsMechanisms")->get();
       /* try {
            $date = new DateTime("NOW", new DateTimeZone('Africa/Luanda'));
            $socketData = [
                "company_id" => "5417167444-2",
                "type" => "mensage",
                "method" => "show",
                "info" => [
                    "titulo"=>"Notificação",
                    "subtitulo"=>"Web Socket",
                    "corpo"=> "Mensagem para teste de notifição usando webSocket",
                    "email"=> "mildaquituxi25@gmail.com"
                ],
                "respostas" => [
                    "sim" => 1,
                    "nao" => 0,
                    "cancelar" => null
                ],
                "time" => $date->format('Y-m-d H:i:s')
            ];
            Util::sendMessageWebSocket($socketData);
            return $stores;

        } catch (\Throwable $th) {
           return $stores;
        }
        //Util::sendMessageWebSocket($stores);*/
        return $stores;
    }
    /**
     * Create a new online store.
     *
     * @param array $data
     * @return \App\Models\OnlineStore
     */
    public static function create($data)
    {

        if (isset($data['payments_mechanisms']) && is_array($data['payments_mechanisms'])) {
            $onlineStore = OnlineStore::updateOrCreate(
                ['CompanyID' => $data['CompanyID']],
                $data
            );
            $onlineStore->company->onlinePaymentsMechanisms()->delete();
            $onlineStore->company->onlinePaymentsMechanisms()->createMany($data['payments_mechanisms']);
            return $onlineStore;
        }
        
    }
    
    /**
     * Update an existing online store.
     *
     * @param \App\Models\OnlineStore $onlineStore
     * @param array $data
     * @return \App\Models\OnlineStore
     */
    public static function update(OnlineStore $onlineStore, $data)
    {
        $onlineStore->update($data);
        
        return $onlineStore;
    }
    
    /**
     * Delete an online store.
     *
     * @param \App\Models\OnlineStore $onlineStore
     * @return void
     * @throws \Exception
     */
    public static function delete(OnlineStore $onlineStore)
    {
        $onlineStore->delete();
    }
    
    /**
     * Get the company associated with an online store.
     *
     * @param \App\Models\OnlineStore $onlineStore
     * @return \App\Models\DocEmpresa|null
     */
    public static function getCompany(OnlineStore $onlineStore)
    {
        return $onlineStore->company;
    }

    public static function getAllCompaniesStoresByNif($nif){
        $company = DocEmpresa::where('TaxRegistrationNumber', $nif)->first();

        if (!$company) {
            return []; // Retorna um array vazio se a empresa não for encontrada
        }
    
        return $company->onlineStores;
    }

    public static function getAllCompaniesStoresWithWebServiceModule($datas){
        $companies = DocEmpresa::whereIn('TaxRegistrationNumber', $datas)->get();

        if ($companies->isEmpty()) {
            return []; // Retorna um array vazio se nenhuma empresa for encontrada
        }
       

        $onlineStores = [];

        foreach ($companies as $company) {
            $onlineStores = array_merge($onlineStores, $company->onlineStores()->get()->toArray());
        }

        return $onlineStores;
    }

    public function getOnlineStoresByNIFs($modulo_id)
    {
        // Fetch the NIFs from the endpoint
        $response = file_get_contents("https://cacimboweb.com/api/empresas/get-nif/licencas/modulo/{$modulo_id}");
        $nifs = json_decode($response, true);
        // Retrieve companies with the NIFs
        $onlineStores = OnlineStore::whereHas('company', function($q) use($nifs){
            $q->whereIn('TaxRegistrationNumber', $nifs);
        })->with('company.onlinePaymentsMechanisms')->get();
        return $onlineStores;
    }
    public function getOnlineStoresByNIFs1($modulo_id)
    {
        // Fetch the NIFs from the endpoint
        $response = file_get_contents("https://cacimboweb.com/api/empresas/get-nif/licencas/modulo/{$modulo_id}");
        $nifs = json_decode($response, true);

        // Retrieve companies with the NIFs
        $companies = DocEmpresa::with('onlinePaymentsMechanisms')
        ->whereIn('TaxRegistrationNumber', $nifs)
        ->get();

        $onlineStores = new Collection();

        foreach ($companies as $company) {
            $onlineStores = $onlineStores->merge($company->onlineStores()->get());
        }
            //dd($onlineStores);

        return $onlineStores;
    }
}
