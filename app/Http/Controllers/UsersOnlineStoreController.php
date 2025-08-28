<?php

namespace App\Http\Controllers;

use App\Http\Services\UsersOnlineStoreService;
use Illuminate\Http\Request;

class UsersOnlineStoreController extends Controller
{
    protected $usersOnlineStoreService;

    public function __construct(UsersOnlineStoreService $usersOnlineStoreService)
    {
        $this->usersOnlineStoreService = $usersOnlineStoreService;
    }

    public function index()
    {
        $usersOnlineStores = $this->usersOnlineStoreService->all();
        return response()->json($usersOnlineStores);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'user_nif' => 'required',
            'online_store_id' => 'required|integer',
        ]);

        $usersOnlineStore = $this->usersOnlineStoreService->create($data);
        return response()->json($usersOnlineStore, 201);
    }

    public function show($id)
    {
        $usersOnlineStore = $this->usersOnlineStoreService->find($id);
        return response()->json($usersOnlineStore);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'user_id' => 'integer',
            'online_store_id' => 'integer',
        ]);

        $usersOnlineStore = $this->usersOnlineStoreService->update($id, $data);
        return response()->json($usersOnlineStore);
    }

    public function destroy($id)
    {
        $this->usersOnlineStoreService->delete($id);
        return response()->json(null, 204);
    }
}
