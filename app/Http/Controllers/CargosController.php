<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CargosController extends Controller
{
    function consultaCargos($id_uni){
        $unidades = \DB::table('cargos')
        ->where('cargos.idarea', $id_uni)
        ->orderBy('idcargo', 'asc')
        ->get();
        return $unidades;
    }
}
