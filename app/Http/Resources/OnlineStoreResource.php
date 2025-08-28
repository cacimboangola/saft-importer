<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OnlineStoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'CompanyID' => $this->CompanyID,
            "ArmazemID" => $this->ArmazemID,
            'StoreName' => $this->StoreName,
            'gps_lat' => $this->gps_lat,
            'gps_long' => $this->gps_long,
            'categoria' => $this->categoria,
            'StoreLogoUrl' => "https://cacimboerp.cacimboweb.com/".$this->StoreLogoUrl,
            'StoreSlogan' => $this->StoreSlogan,
            'company' =>$this->company->load("onlinePaymentsMechanisms"),
        ];
    }
}
