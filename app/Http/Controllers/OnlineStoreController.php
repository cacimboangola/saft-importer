<?php

namespace App\Http\Controllers;


use App\Http\Resources\OnlineStoreResource;
use App\Http\Services\OnlineStoreService;
use App\Models\OnlineStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnlineStoreController extends Controller
{
    protected $onlineStoreService;
    
    public function __construct(OnlineStoreService $onlineStoreService)
    {
        $this->onlineStoreService = $onlineStoreService;
    }

    /**
     * Get all online stores.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $onlineStores = $this->onlineStoreService->getAllOnlineStores();
        
        // Transform the online stores into a collection of resources
       return OnlineStoreResource::collection($onlineStores);
    }
    
    /**
     * Create a new online store.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data["CompanyID"] = $request->CompanyID;
        $data["StoreName"] = $request->StoreName;
        if ($request->hasFile('StoreLogoUrl')) {
            $logoImage = $request->file('StoreLogoUrl');
            $path = $logoImage->move('logos',$logoImage);
            $data["StoreLogoUrl"] = $path; 
        }else{
            $data["StoreLogoUrl"] = $request->StoreLogoUrl;
        }
        $data["StoreSlogan"] = $request->StoreSlogan; 
        $data["ArmazemID"] = $request->ArmazemID;
        $data["payments_mechanisms"] = json_decode($request->payments_mechanisms, true); 

        //return response()->json($data["payments_mechanisms"], 200);

        // Create the online store
        $onlineStore = $this->onlineStoreService->create($data);
        // Transform the online store into a resource
        if ($onlineStore) {
            return new OnlineStoreResource($onlineStore);
        }else{
            abort(404);
        }
        
    }
    
    /**
     * Update an existing online store.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\OnlineStore $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function update(OnlineStore $onlineStore, Request $request)
    {
        $data["CompanyID"] = $request->CompanyID;
        $data["StoreName"] = $request->StoreName;
        $data["StoreSlogan"] = $request->StoreSlogan;
        if ($request->hasFile('StoreLogoUrl')) {
            $logoFile = $request->file('StoreLogoUrl');
            if ($onlineStore->StoreLogoUrl) {
                Storage::delete($onlineStore->StoreLogoUrl);
            }
            $logoPath = $logoFile->move('logos');
            $data['StoreLogoUrl'] = $logoPath;
        }
                       
        // Update the online store
        $updatedOnlineStore = $this->onlineStoreService->update($onlineStore, $data);
        
        // Transform the updated online store into a resource
        return new OnlineStoreResource($updatedOnlineStore);
    }
    
    /**
     * Delete an online store.
     *
     * @param \App\Models\OnlineStore $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function destroy(OnlineStore $onlineStore)
    {
        $this->onlineStoreService->delete($onlineStore);
        
        return response()->json(null, 204);
    }
    
    /**
     * Get the company associated with an online store.
     *
     * @param \App\Models\OnlineStore $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function getCompany(OnlineStore $onlineStore)
    {
        $company = $this->onlineStoreService->getCompany($onlineStore);
        
        return response()->json($company, 200);
    }

    public function getOnlineStoresByNIFs($modulo_id) {
        
        $onlineStores = $this->onlineStoreService->getOnlineStoresByNIFs($modulo_id);
        
        // Transform the online stores into a collection of resources
       return OnlineStoreResource::collection($onlineStores);
    }
}
