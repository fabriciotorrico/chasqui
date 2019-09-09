<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DateTime;

use App\User;
use App\Gestion;
use App\Usada;

class PruebasController extends Controller
{
    //
    public function form_pruebas(){
        $user_id = Auth::user()->id;
        $usuario=User::find($user_id);

        $hoy = new DateTime(date('Y-m-d'));

        // $vigentes = Gestion::where('id_usuario', $user_id)
        // ->where('vigencia', '>', $hoy)
        // ->orderBy('id', 'asc')->get();

        // $vigentes = \DB::table('gestiones')
        // ->leftjoin('usadas', 'gestiones.id_usuario', '=', 'usadas.id_usuario')
        // ->selectRaw('gestiones.*, count(usadas.usadas) as total')
        // ->where('vigencia', '>', $hoy)
        // ->groupBy('usadas.id_usuario')
        // ->get();
        $ultima_gestion = Gestion::where('id_usuario', $user_id)->where('vigencia', '>', $hoy)->orderBy('id', 'desc')->take(1)->get();
        $vigentes = Gestion::select(\DB::raw('gestiones.*, SUM(usadas.usadas) as total'), 'usadas.id_gestion as id_gestion')
         ->leftJoin('usadas', 'gestiones.id', '=', 'usadas.id_gestion')
         ->where('vigencia', '>', $hoy)
         ->where('gestiones.id_usuario', $user_id)
        //  ->where('gestiones.id', '=', 'usadas.id_gestion')
         ->groupBy('gestiones.id')
         ->get();

         $user_id = Auth::user()->id;
         $hoy = new DateTime(date('Y-m-d'));
         $disponibles = \DB::table('users')
         ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
         ->where('users.id', $user_id)
         ->where('gestiones.vigencia', '>', $hoy)
         ->select(\DB::raw('SUM(gestiones.saldo) as saldo_total'))->get();
        //  array_merge(['item'=>$item->toArray()], ['chef' => $chef->toArray()]);
        $usadas = Usada::where('id_usuario', $user_id)->where('id_solicitud', 58)->orderBy('start', 'asc')->get();
        foreach ($usadas as $key => $value) {
            $usadas[$key] = array_add($value, 'disponible', $disponibles[0]->saldo_total);
            // $usadas[$key]['disponible'] = $disponibles[0]->saldo_total;
        }
        
        return view('formularios.form_pruebas')
        ->with('vigentes', $vigentes)
        ->with('ultima_gestion', $ultima_gestion)
        ->with('usadas', $usadas)
        ->with('disponibles', $disponibles);

    }
}
