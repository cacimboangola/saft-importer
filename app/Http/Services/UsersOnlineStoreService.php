<?php

namespace App\Http\Services;

use App\Models\UsersOnlineStore;

class UsersOnlineStoreService
{
    protected $usersOnlineStore;

    public function __construct(UsersOnlineStore $usersOnlineStore)
    {
        $this->usersOnlineStore = $usersOnlineStore;
    }
    
    public function all()
    {
        return $this->usersOnlineStore->with('expositor')->get();
    }
    public function create(array $data)
    {
        return $this->usersOnlineStore->create($data);
    }

    public function find($id)
    {
        return $this->usersOnlineStore->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $usersOnlineStore = $this->find($id);
        $usersOnlineStore->update($data);

        return $usersOnlineStore;
    }
    
    public static function getOnlineStoresByUserId($userId)
    {
        return UsersOnlineStore::where('user_id', $userId)->pluck('online_store_id');
    }
    public static function getOnlineStoresByUserNif($user_nif)
    {
        return UsersOnlineStore::where('user_nif', $user_nif)->pluck('online_store_id');
    }

    public function delete($id)
    {
        $usersOnlineStore = $this->find($id);
        return $usersOnlineStore->delete();
    }
}
