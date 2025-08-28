<?php

namespace App\Models;

use App\Http\Services\DocEmpresaService;
use App\Models\Perfil;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $connection = "mysql2";

    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'nif', 'tipo', 'parceiro_id','status', 'notification_token', 'lastCompanyIDUsed', 'id_perfil', 'isSuperAdmin', 'phone_number','phone_number_is_verified','code_verification'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*public function __construct()
    {
        
    }*/

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    public function hasTipo($tipo){
        if ($this->tipo == $tipo) {
            return true;
        } else
        return false;
    }
    public function canLogin()
    {
        if ($this->status == '1') {
            return true;
        }else{
            return false;
        }
    }

    public function getEmpresas(){
        return DocEmpresaService::getEmpresasByUserId($this->id, $this->lastCompanyIDUsed);
    }
  	public function getEmpresasGestHotel(){
        return DocEmpresaService::getEmpresasGestHotelByUserId($this->id, $this->lastCompanyIDUsed);
    }

    public function setLastCompanyUsed($companyId){
        $this->lastCompanyIDUsed = $companyId;
        $this->save();
    }
    
    public function getLastCompanyUsed(){
        if(count(DocEmpresaService::getEmpresasByUserId($this->id, $this->lastCompanyIDUsed)) > 0){
            $this->lastCompanyIDUsed = $this->lastCompanyIDUsed ?? DocEmpresaService::getEmpresasByUserId($this->id,$this->lastCompanyIDUsed)->first()->CompanyID;
            $this->save();
            return $this->lastCompanyIDUsed;
        }
        
    }
  
  public function getLastCompanyUsedGestHotel(){
        if(count(DocEmpresaService::getEmpresasGestHotelByUserId($this->id, $this->lastCompanyIDUsed)) > 0){
            $this->lastCompanyIDUsed = $this->lastCompanyIDUsed ?? DocEmpresaService::getEmpresasGestHotelByUserId($this->id,$this->lastCompanyIDUsed)->first()->CompanyID;
            $this->save();
            return $this->lastCompanyIDUsed;
        }
        
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil');
    }

    public function user_empresas()
    {
        return $this->hasMany(UserEmpresa::class);
    }

    /**
     *
     * public function setPasswordAttribute($password)
     * {
     *    $this->attributes['password'] = bcrypt($password);
     *}

     *public function getNameAttribute($name)
     *{
     *    return ucfirst($name);
     *}
     */
}
