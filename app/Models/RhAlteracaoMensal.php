<?php

namespace App\Models;

use App\Models\RhFuncionario;
use App\Models\RhRubrica;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RhAlteracaoMensal extends Model
{
    use HasFactory;

    public $table = 'rhAlteracoesMensais';
    public $un = 'un';
    
    protected $fillable = ['empresa', 'funcionario', 'data', 'quantidade', 'valor', 'motivo', 'rubrica', 'un'];

    public function getRubricaAttribute(){
       $data = RhRubrica::where('id', $this->attributes['rubrica'])->first()->descricao ?? $this->attributes['rubrica'];  
       return $data;      
    }
    public function getUnAttribute(){
        $data = RhRubrica::where('id', $this->attributes['un'])->first()->un ?? $this->un;  
        $this->un = $data;  
        return $this->un;
    }
    public function getFuncionarioAttribute(){
        $data = RhFuncionario::where('id', $this->attributes['funcionario'])->first()->nome ?? $this->attributes['funcionario'];
        return $data;
    }
}
