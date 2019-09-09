<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use App\Derivacion;

class CorrespondenciasController extends Controller
{
  //Funcion para obtener todos los datos de la persona logueada
  public function datos_persona_logueada(){
    $usuario_logueado = \DB::table('personas')
    ->join('users', 'personas.id_persona', '=', 'users.id_persona')
    ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
    ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
    ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
    ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo', 'personas.nombre_foto',
            'cargos.id_cargo', 'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.id_area', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
            'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area', 'servidores_publicos.id_servidor_publico')
    ->where('users.id', Auth::user()->id)
    ->get();
    return $usuario_logueado;
  }

  //Funcion interna para obtener la cantidad de correspondencia nueva (sin recepcionar)
  public function bandeja_nuevos_cantidad(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
    }

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = \DB::table('chasqui_derivaciones')
        ->where('chasqui_derivaciones.id_cargo_destino', $id_cargo_logueado)
        ->where('chasqui_derivaciones.recibido', 0)
        ->count();
    return $bandeja_nuevos_cantidad;
  }

  //Funcion interna para obtener el listado de las derivaciones recibidas del usuario logueado
  public function derivaciones_recibidas(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
    }

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";

    //Tomamos las derivaciones a mostrar
    $derivaciones = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
        ->where('chasqui_derivaciones.id_cargo_destino', $id_cargo_logueado)
        ->where('chasqui_derivaciones.derivado', 0)
        ->where('chasqui_derivaciones.anulado', 0)
        ->select('chasqui_derivaciones.id_derivacion', 'personas.nombres', 'personas.paterno', 'personas.materno', 'areas.sigla', 'chasqui_instrucciones.descripcion as instruccion',
                  'chasqui_derivaciones.*')
        ->orderby('chasqui_derivaciones.id_derivacion', 'DESC')
        ->get();
    return $derivaciones;
  }

  //Funcion interna para obtener el plazo restante de las derivaciones con prioridad alta
  public function plazo_restante_derivaciones(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
    }

    //Tomamos las derivaciones recibidas del usuario logueado
    $derivaciones = $this->derivaciones_recibidas();

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();

    $array_plazo = array();

    //Obtenemos el plazo para obtener el tiempo restante de cada derivacion, armamos un array y lo mandamos
    foreach ($derivaciones as $derivacion) {
      $plazo = $derivacion->plazo;
      $fecha_creacion = $derivacion->fecha_creacion;

      //Ponemos en el formato correcto
      $fecha_creacion = Carbon::parse($fecha_creacion);

      //Restamos la fecha actual menos la de envio
      $dif_fechas = $fecha_creacion->diffInDays($hoy);

      //Verificamos el plazo restante
      $plazo_restante = $plazo - $dif_fechas;

      //Agregamos al array
      $array_plazo[] = $plazo_restante;
    }

    return $array_plazo;
  }

  //Funcion interna para pedir el cite segun el correlativo disponible
  public function get_cite_hoja_ruta_interna(){
    //Tomamos el año en curso
    $date = new Carbon();
    $hoy = Carbon::now();
    $año = $hoy->format('Y');

    //Tomamos el correlativo para el cite
    $correlativo_cite = \DB::table('chasqui_correlativos')
    ->where('id_tipo_documento', 2)
    ->where('gestion', $año)
    ->value('correlativo');

    //Si no existe el registro para el correlativo, lo creamos
    if ($correlativo_cite < 1) {
      //Tomamos el año (nuevamente por que se pierde)
      $date = new Carbon();
      $hoy = Carbon::now();
      $año = $hoy->format('Y');

      //Creamos el registro
      \DB::table('chasqui_correlativos')->insert([
          ['id_area' => 0,
           'id_tipo_documento' => 2,
           'correlativo' => 1,
           'gestion' => $año]
      ]);
      //Establecemos el $correlativo_cite en 1
      $correlativo_cite = 1;
    }

    $cite = "MDCyT-HRI-".$correlativo_cite."/".$año;

    //Devolvemos el cite generado
    return $cite;
  }

  //Funcion interna para pedir el cite segun el correlativo disponible
  public function get_cite_comunicacion_interna(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $persona_origen = $usuario->nombres." ".$usuario->paterno." ".$usuario->materno;
      $id_cargo_origen = $usuario->id_cargo;
      $cargo_origen = $usuario->descripcion_del_cargo;
      $sigla_origen = $usuario->sigla_del_area;
      $id_nivel_jerarquico_origen = $usuario->id_nivel_jerarquico;
      $id_area_origen = $usuario->id_area;
      $unidad_origen = $usuario->unidad;
      $id_direccion_origen = $usuario->id_direccion;
      $id_entidad_origen = $usuario->id_entidad;
      $id_servidor_publico_origen = $usuario->id_servidor_publico;
    }

    //Tomamos el año en curso
    $date = new Carbon();
    $hoy = Carbon::now();
    $año = $hoy->format('Y');

    //Tomamos el correlativo para el cite
    $correlativo_cite = \DB::table('chasqui_correlativos')
    ->where('id_area', $id_area_origen)
    ->where('id_tipo_documento', 1)
    ->where('gestion', $año)
    ->value('correlativo');

    //Si no existe el registro para el correlativo, lo creamos
    if ($correlativo_cite < 1) {
      //Tomamos el año (nuevamente por que se pierde)
      $date = new Carbon();
      $hoy = Carbon::now();
      $año = $hoy->format('Y');

      //Creamos el registro
      \DB::table('chasqui_correlativos')->insert([
          ['id_area' => $id_area_origen,
           'id_tipo_documento' => 1,
           'correlativo' => 1,
           'gestion' => $año]
      ]);
      //Establecemos el $correlativo_cite en 1
      $correlativo_cite = 1;
    }

    //Tomamos las siglas de unidad, direccion y ministerio para formar el cite segun sus dependencias
    $sigla_entidad = \DB::table('areas')
    ->where('id_entidad', $id_entidad_origen)
    ->where('id_direccion', 0)
    ->where('unidad', 0)
    ->value('sigla');

    //Verificamos si tiene direccion, si es 0, signigica que depende de despacho
    if ($id_direccion_origen == 0) {
      //Tomamos el año (nuevamente por que se pierde y genera conflicto)
      $date = new Carbon();
      $hoy = Carbon::now();
      $año = $hoy->format('Y');

      //Verificamos si es una unidad dependiente de despacho
      if ($unidad_origen != 0) {
        // Si es una unidad dependiente de despacho, tomamos la sigla de la unidad
        $sigla_unidad = \DB::table('areas')
        ->where('id_area', $id_area_origen)
        ->value('sigla');
        //Aramamos el cite como una unidad dependiente de despacho
        $cite = $sigla_entidad."-".$sigla_unidad."-CI Nº ".$correlativo_cite."/".$año;
      }
      else {
        //Aramamos el cite como un cargo dependiente de despacho
        $cite = $sigla_entidad."-DESP-CI Nº ".$correlativo_cite."/".$año;
      }
    }
    else {
      //Tomamos el año (nuevamente por que se pierde y genera conflicto)
      $date = new Carbon();
      $hoy = Carbon::now();
      $año = $hoy->format('Y');

      //En caso que tenga direccion, tomamos la sigla
      $sigla_direccion = \DB::table('areas')
      ->where('id_entidad', $id_entidad_origen)
      ->where('id_direccion', $id_direccion_origen)
      ->where('unidad', 0)
      ->value('sigla');
      //Verificamos si tiene unidad, si es 0 significa que depende de la direccion
      if ($unidad_origen == 0) {
        //Aramamos el cite como direccion
        $cite = $sigla_entidad."-".$sigla_direccion."-CI Nº ".$correlativo_cite."/".$año;
      }
      else {
        //En caso que si tenga unidad, tomamos la sigla
        $sigla_unidad = \DB::table('areas')
        ->where('id_area', $id_area_origen)
        ->value('sigla');
        //Aramamos el cite como unidad
        $cite = $sigla_entidad."-".$sigla_direccion."-".$sigla_unidad."-CI Nº ".$correlativo_cite."/".$año;
      }
    }

    //Devolvemos el cite generado
    return $cite;
  }

  //Funcion interna para tomar destinatarios
  public function get_destinatarios(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $persona_origen = $usuario->nombres." ".$usuario->paterno." ".$usuario->materno;
      $id_cargo_origen = $usuario->id_cargo;
      $cargo_origen = $usuario->descripcion_del_cargo;
      $sigla_origen = $usuario->sigla_del_area;
      $id_nivel_jerarquico_origen = $usuario->id_nivel_jerarquico;
      $id_area_origen = $usuario->id_area;
      $unidad_origen = $usuario->unidad;
      $id_direccion_origen = $usuario->id_direccion;
      $id_entidad_origen = $usuario->id_entidad;
      $id_servidor_publico_origen = $usuario->id_servidor_publico;
    }

    //Tomamos todos los posibles destinatarios segun regla general
    switch ($id_nivel_jerarquico_origen) {
      case 10:
        //En caso que sea tecnico / coordinador, seleccionamos el jefe de su unidad o director segun corresponda
        //Si su unidad es 0, pertenecen a una direccion y le mandan al director, caso contrario, le mandan al jefe de la unidad
        if ($unidad_origen == 0) {
          //Si pertenecen a una direccion, tomamos los datos del director
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 30)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->where('areas.unidad', 0)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        else {
          //Si pertenecen a una unidad, tomamos los datos del jefe de unidad
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 20)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->where('areas.unidad', $unidad_origen)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        break;

      case 20:
        //En caso que sea jefe de unidad, seleccionamos el director o ministro segun corresponda y sus dependientes
        //Si su direccion es 0, depende de despacho y le mandan al ministro
        if ($id_direccion_origen == 0) {
          //Si depende de despacho, tomamos los datos de sus sus dependientes y del ministro
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 50)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 10)
          ->where('areas.unidad', $unidad_origen)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        else {
          //Si depende de una direccion, tomamos los datos de sus dependientes y del director
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 30)
          ->where('areas.unidad', 0)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 10)
          ->where('areas.unidad', $unidad_origen)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        break;

      case 30:
        //En caso que sea director, seleccionamos el viceministro o ministro segun corresponda, todos los directores del mismo ministerio y sus dependientes
        //Verificamos si depende del ministerio o de un viceministro
        if ($id_entidad_origen == 1) {
          //Si depende del MDCyT, tomamos los datos de sus sus dependientes, de los directores, jefes de unidad sin direccion y del ministro
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 50)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 45)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 30)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('servidores_publicos.id_servidor_publico', 'not like', $id_servidor_publico_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 20)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 20)
          ->where('areas.id_direccion', 0)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        else {
          //Si depende de un viceministerio, tomamos los datos de sus sus dependientes, de los directores, jefes de unidad sin direccion y del viceministro
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 40)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 45) //Verificar si es 45 o 35
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 30)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('servidores_publicos.id_servidor_publico', 'not like', $id_servidor_publico_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 20)
          ->where('areas.id_direccion', $id_direccion_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        }
        break;

      case 35:
        //En caso que sea un cargo que depende directamente de despacho de un viceministerio, seleccionamos al viceministro, directores, jefes de unidad sin direccion y usuarios con nivel jerarquico 35
        $destinatarios = \DB::table('personas')
        ->join('users', 'personas.id_persona', '=', 'users.id_persona')
        ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
        ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
        ->where('cargos.id_nivel_jerarquico', 40)
        ->where('areas.id_entidad', $id_entidad_origen)
        ->where('cargos.vigente', 1)
        ->where('servidores_publicos.activo', 1)
        ->orwhere('cargos.id_nivel_jerarquico', 35)
        ->where('areas.id_entidad', $id_entidad_origen)
        ->where('servidores_publicos.id_servidor_publico', 'not like', $id_servidor_publico_origen)
        ->where('cargos.vigente', 1)
        ->where('servidores_publicos.activo', 1)
        ->orwhere('cargos.id_nivel_jerarquico', 30)
        ->where('areas.id_entidad', $id_entidad_origen)
        ->where('cargos.vigente', 1)
        ->where('servidores_publicos.activo', 1)
        ->orderby('cargos.id_cargo')
        ->get();
        break;

      case 40:
        //En caso que sea viceministro, seleccionamos otros viceministros, ministro, todos los directores del mismo viceministro y sus dependientes
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 50)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 40)
          ->where('servidores_publicos.id_servidor_publico', 'not like', $id_servidor_publico_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 30)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        break;

      case 45:
        //En caso que sea un cargo que depende directamente de despacho (asesor, jefe de gabinete, etc), seleccionamos al ministro, directores, jefes de unidad sin direccion y usuarios con nivel jerarquico 45
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 50)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 45)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('servidores_publicos.id_servidor_publico', 'not like', $id_servidor_publico_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 30)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 20)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('areas.id_direccion', 0)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();

        break;

      case 50:
        //En caso que sea el ministro, seleccionamos al ministro, directores, jefes de unidad sin direccion y usuarios con nivel jerarquico 45
          $destinatarios = \DB::table('personas')
          ->join('users', 'personas.id_persona', '=', 'users.id_persona')
          ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
          ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
                  'cargos.descripcion as descripcion_del_cargo', 'cargos.id_nivel_jerarquico', 'areas.unidad', 'areas.id_direccion', 'areas.id_entidad',
                  'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
          ->where('cargos.id_nivel_jerarquico', 45)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 40)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 30)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orwhere('cargos.id_nivel_jerarquico', 20)
          ->where('areas.id_entidad', $id_entidad_origen)
          ->where('areas.id_direccion', 0)
          ->where('cargos.vigente', 1)
          ->where('servidores_publicos.activo', 1)
          ->orderby('cargos.id_cargo')
          ->get();
        break;

      default:
        // Si hay un nivel jerarquico nuevo, se deberà agregar las reglas correspondientes
        break;
    }
    return $destinatarios;
  }




  public function form_nueva_correspondencia(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $persona_origen = $usuario->nombres." ".$usuario->paterno." ".$usuario->materno;
      $id_cargo_origen = $usuario->id_cargo;
      $cargo_origen = $usuario->descripcion_del_cargo;
      $sigla_origen = $usuario->sigla_del_area;
      $id_nivel_jerarquico_origen = $usuario->id_nivel_jerarquico;
      $id_area_origen = $usuario->id_area;
      $unidad_origen = $usuario->unidad;
      $id_direccion_origen = $usuario->id_direccion;
      $id_entidad_origen = $usuario->id_entidad;
      $id_servidor_publico_origen = $usuario->id_servidor_publico;
    }

    //Tomamos todos los posibles destinatarios segun regla general
    $destinatarios = $this->get_destinatarios();

    //Además del flujo normal, se tiene la tabla chasqui_flujos_especiales, donde se agregan flujos de derivacion especiales uno a uno, seleccionamos ese para el actual usuario
    $destinatarios_especiales = \DB::table('chasqui_flujos_especiales')
    ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_flujos_especiales.id_cargo_destino')
    ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
    ->join('servidores_publicos', 'servidores_publicos.id_cargo', '=', 'cargos.id_cargo')
    ->join('personas', 'personas.id_persona', '=', 'servidores_publicos.id_persona')
    ->join('users', 'users.id_persona', '=', 'personas.id_persona')
    ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
            'cargos.descripcion as descripcion_del_cargo', 'areas.sigla as sigla_del_area')
    ->where('chasqui_flujos_especiales.id_cargo_origen', $id_cargo_origen)
    ->where('chasqui_flujos_especiales.activo', 1)
    ->where('cargos.vigente', 1)
    ->where('servidores_publicos.activo', 1)
    ->where('users.activo', 1)
    ->orderby('cargos.id_cargo')
    ->get();

    //Tomamos el cite disponible para usarlo
    $cite = $this->get_cite_hoja_ruta_interna();

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();
    $fecha = $hoy->format('Y-m-d');

    //Tomamos las posibles instrucciones
    $instrucciones = \DB::table('chasqui_instrucciones')
    ->where('activo', 1)
    ->select('*')
    ->get();

    //Establecemos que la accion a realizar sea enviar nueva correspondencia
    $accion = "enviar_nueva_correspondencia";

    //Devolvemos la vista con los parametros necesarios
    return view("formularios_chasqui.form_nueva_correspondencia")
            ->with("persona_origen", $persona_origen)
            ->with('cargo_origen', $cargo_origen)
            ->with('sigla_origen', $sigla_origen)
            ->with('destinatarios', $destinatarios)
            ->with('destinatarios_especiales', $destinatarios_especiales)
            ->with('cite', $cite)
            ->with('instrucciones', $instrucciones)
            ->with('fecha', $fecha)
            ->with('accion', $accion);
  }

  public function form_derivar_correspondencia($id_derivacion){
    //echo "ID: ".$id_derivacion;
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $persona_origen = $usuario->nombres." ".$usuario->paterno." ".$usuario->materno;
      $id_cargo_origen = $usuario->id_cargo;
      $cargo_origen = $usuario->descripcion_del_cargo;
      $sigla_origen = $usuario->sigla_del_area;
      $id_nivel_jerarquico_origen = $usuario->id_nivel_jerarquico;
      $id_area_origen = $usuario->id_area;
      $unidad_origen = $usuario->unidad;
      $id_direccion_origen = $usuario->id_direccion;
      $id_entidad_origen = $usuario->id_entidad;
      $id_servidor_publico_origen = $usuario->id_servidor_publico;
    }

    //Tomamos todos los posibles destinatarios segun regla general
    $destinatarios = $this->get_destinatarios();

    //Además del flujo normal, se tiene la tabla chasqui_flujos_especiales, donde se agregan flujos de derivacion especiales uno a uno, seleccionamos ese para el actual usuario
    $destinatarios_especiales = \DB::table('chasqui_flujos_especiales')
    ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_flujos_especiales.id_cargo_destino')
    ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
    ->join('servidores_publicos', 'servidores_publicos.id_cargo', '=', 'cargos.id_cargo')
    ->join('personas', 'personas.id_persona', '=', 'servidores_publicos.id_persona')
    ->join('users', 'users.id_persona', '=', 'personas.id_persona')
    ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
            'cargos.descripcion as descripcion_del_cargo', 'areas.sigla as sigla_del_area')
    ->where('chasqui_flujos_especiales.id_cargo_origen', $id_cargo_origen)
    ->where('chasqui_flujos_especiales.activo', 1)
    ->where('cargos.vigente', 1)
    ->where('servidores_publicos.activo', 1)
    ->where('users.activo', 1)
    ->orderby('cargos.id_cargo')
    ->get();

    //Tomamos el cite de la id_derivacion recibida
    $cite = \DB::table('chasqui_derivaciones')
    ->where('id_derivacion', $id_derivacion)
    ->value('cite');
    //->get();

    //$cite = $this->get_cite_comunicacion_interna();

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();
    $fecha = $hoy->format('Y-m-d');

    //Tomamos las posibles instrucciones
    $instrucciones = \DB::table('chasqui_instrucciones')
    ->where('activo', 1)
    ->select('*')
    ->get();

    //Establecemos que la accion a realizar sea derivar la correspondencia
    $accion = "derivar_correspondencia";

    //Devolvemos la vista con los parametros necesarios
    return view("formularios_chasqui.form_nueva_correspondencia")
            ->with("persona_origen", $persona_origen)
            ->with('cargo_origen', $cargo_origen)
            ->with('sigla_origen', $sigla_origen)
            ->with('destinatarios', $destinatarios)
            ->with('destinatarios_especiales', $destinatarios_especiales)
            ->with('cite', $cite)
            ->with('instrucciones', $instrucciones)
            ->with('fecha', $fecha)
            ->with('accion', $accion)
            ->with('id_derivacion', $id_derivacion);
  }

  public function derivar_correspondencia(Request $request){
    //Tomamos los valores del post
    $id_derivacion_respondida = $request->id_derivacion;
    $cite = $request->cite;
    $id_usuario_destino = $request->id_usuario_destino;
    $id_instruccion = $request->id_instruccion;
    $referencia = $request->referencia;
    $contenido = $request->contenido;
    //$id_documento = $request->id_documento;
    $id_documento = 0;
    $nro_paginas_agregadas = $request->nro_paginas_agregadas;
    $prioridad = $request->prioridad;
    $switch_copia = $request->switch_copia; //Si esta en "on" es seleccionado
    $id_usuarios_copia = $request->id_usuario_copia;

    //Establecemos el campo plazo segun corresponda
    if ($request->plazo > 0) {$plazo = $request->plazo;}
    else {$plazo = 0;}

    //Tomamos otros campos necesarios para el registro de la derivacion
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_persona_origen = $usuario->id_persona;
      $id_cargo_origen = $usuario->id_cargo;
      $id_area_origen = $usuario->id_area;
    }

    //Tomamos los datos del destinatario
    $id_persona_destino = \DB::table('users')
        ->where('users.id', $id_usuario_destino)
        ->where('users.activo', 1)
        ->value('users.id_persona');

    $id_cargo_destino = \DB::table('users')
        ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
        ->where('users.id', $id_usuario_destino)
        ->where('users.activo', 1)
        ->value('servidores_publicos.id_cargo');

    //Tomamos la fecha y horactual actual y gestion
    $date = new Carbon();
    $hoy = Carbon::now();
    $fecha_creacion = $hoy->format('Y-m-d H');
    $gestion = $hoy->format('Y');

    //Realizamos un nuevo registro
    $derivacion =new Derivacion;
    $derivacion->cite=$cite;
    $derivacion->id_cargo_origen=$id_cargo_origen;
    $derivacion->id_persona_origen=$id_persona_origen;
    $derivacion->id_cargo_destino=$id_cargo_destino;
    $derivacion->id_persona_destino=$id_persona_destino;
    $derivacion->id_instruccion=$id_instruccion;
    $derivacion->referencia=$referencia;
    $derivacion->contenido=$contenido;
    $derivacion->id_documento=$id_documento;
    $derivacion->nro_paginas_agregadas=$nro_paginas_agregadas;
    $derivacion->prioridad=$prioridad;
    $derivacion->plazo=$plazo;
    $derivacion->tipo="Original";
    $derivacion->recibido=0;
    $derivacion->fecha_recibido="NULL";
    $derivacion->derivado=0;
    $derivacion->fecha_derivado="NULL";
    $derivacion->gestion=$gestion;
    $derivacion->anulado=0;
    $derivacion->save();

    //Tomamos el id del registro realizado
    $id_derivacion_creada = $derivacion->id;

    //Verficamos si se debe enviar el documento como copia
    if ($switch_copia == "on") {

      //Agregamos la palabra COPIA a la referencia
      $referencia_copia = $referencia." - COPIA";

      //Para cada usuario como copia
      for ($i=0;$i<count($id_usuarios_copia);$i++)
      {
        //Tomamos los datos de las personas a enviar como copia
        $id_persona_destino_copia = \DB::table('users')
            ->where('users.id', $id_usuarios_copia[$i])
            ->where('users.activo', 1)
            ->value('users.id_persona');

        $id_cargo_destino_copia = \DB::table('users')
            ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
            ->where('users.id', $id_usuarios_copia[$i])
            ->where('users.activo', 1)
            ->value('servidores_publicos.id_cargo');

        //Realizamos el registro de las copias, solo si es diferente al destinatario original
        if ($id_cargo_destino_copia != $id_usuario_destino && $id_persona_destino_copia != $id_persona_destino) {
          \DB::table('chasqui_derivaciones')->insert([
              ['cite' => $cite,
               'id_cargo_origen' => $id_cargo_origen,
               'id_persona_origen' => $id_persona_origen,
               'id_cargo_destino' => $id_cargo_destino_copia,
               'id_persona_destino' => $id_persona_destino_copia,
               'id_instruccion' => $id_instruccion,
               'referencia' => $referencia_copia,
               'contenido' => $contenido,
               'id_documento' => $id_documento,
               'nro_paginas_agregadas' => $nro_paginas_agregadas,
               'prioridad' => $prioridad,
               'plazo' => $plazo,
               'tipo' => 'Copia',
               'recibido' => 0,
               'fecha_recibido' => 'NULL',
               'derivado' => 0,
               'fecha_derivado' => 'NULL',
               'gestion' => $gestion,
               'anulado' => 0]
          ]);
        }
      }
    }

    //Actualizamos el valor derivado de la correspondencia recibida
    \DB::table('chasqui_derivaciones')
        ->where('id_derivacion', $id_derivacion_respondida)
        ->update(['derivado' => 1,
                  'fecha_derivado' => $fecha_creacion]);

    //Tomamos todos los campos para enviar a la vista bandeja de entrada
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";
    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();
    //Tomamos las derivaciones a mostrar
    $derivaciones = $this->derivaciones_recibidas();
    //Tomamos el plazo que se tiene para responder cada derivacion
    $array_plazo = $this->plazo_restante_derivaciones();
    //Establecemos el mensaje de exito a mostrar
    $mensaje_exito = "La correspondencia fue derivada correctamente.";

    //Establecemos las variables que definiran el mensaje de exito a mostrar y el id a imprimir en la funcion msj_exitoso_imprimir
    $accion_exitosa = "hoja_ruta_interna_derivada";
    $id_derivacion = $id_derivacion_creada;

    //Codificamos los parametros a pasar con get
    $accion_exitosa_codificado = base64_encode($accion_exitosa);
    $id_derivacion_codificado = base64_encode($id_derivacion);

    //return $this->vista_exitoso();
    return redirect('msj_exitoso_imprimir/'.$accion_exitosa_codificado.'/'.$id_derivacion_codificado);
  }


  public function nueva_correspondencia(Request $request){
    //Tomamos los valores del post
    $id_usuario_destino = $request->id_usuario_destino;
    $id_instruccion = $request->id_instruccion;
    $referencia = $request->referencia;
    $contenido = $request->contenido;
    //$id_documento = $request->id_documento;
    $id_documento = 0;
    $nro_paginas_agregadas = $request->nro_paginas_agregadas;
    $prioridad = $request->prioridad;
    $switch_copia = $request->switch_copia; //Si esta en "on" es seleccionado
    $id_usuarios_copia = $request->id_usuario_copia;

    //Establecemos el campo plazo segun corresponda
    if ($request->plazo > 0) {$plazo = $request->plazo;}
    else {$plazo = 0;}

    //Establecemos el campo instruccion_otro segun corresponda
    if ($request->instruccion_otro != "") {$instruccion_otro = $request->instruccion_otro;}
    else {$instruccion_otro = "";}

    //Tomamos otros campos necesarios para el registro de la derivacion
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_persona_origen = $usuario->id_persona;
      $id_cargo_origen = $usuario->id_cargo;
      $id_area_origen = $usuario->id_area;
    }

    //Tomamos los datos del destinatario
    $id_persona_destino = \DB::table('users')
        ->where('users.id', $id_usuario_destino)
        ->where('users.activo', 1)
        ->value('users.id_persona');

    $id_cargo_destino = \DB::table('users')
        ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
        ->where('users.id', $id_usuario_destino)
        ->where('users.activo', 1)
        ->value('servidores_publicos.id_cargo');

    //Tomamos la fecha y horactual actual y gestion
    $date = new Carbon();
    $hoy = Carbon::now();
    $fecha_creacion = $hoy->format('Y-m-d H');
    $gestion = $hoy->format('Y');

    //Dado que dos personas pudieron haber obtenido el mismo cite por demorar, obtenemos el cite disponible en este momento
    $cite = $this->get_cite_hoja_ruta_interna();

    //Lo utlizamos e incrementamos en 1 el valor del correlativo para comunicaciones internas
    //Tomamos el valor actual del correlativo
    $correlativo_cite = \DB::table('chasqui_correlativos')
    ->where('id_tipo_documento', 2)
    ->where('gestion', $gestion)
    ->value('correlativo');

    $nuevo_correlativo_disponible = $correlativo_cite+1;

    \DB::table('chasqui_correlativos')
        ->where('id_tipo_documento', 2)
        ->where('gestion', $gestion)
        ->update(['correlativo' => $nuevo_correlativo_disponible]);


        //Realizamos un nuevo registro
        $derivacion =new Derivacion;
        $derivacion->cite=$cite;
        $derivacion->id_cargo_origen=$id_cargo_origen;
        $derivacion->id_persona_origen=$id_persona_origen;
        $derivacion->id_cargo_destino=$id_cargo_destino;
        $derivacion->id_persona_destino=$id_persona_destino;
        $derivacion->id_instruccion=$id_instruccion;
        $derivacion->instruccion_otro=$instruccion_otro;
        $derivacion->referencia=$referencia;
        $derivacion->contenido=$contenido;
        $derivacion->id_documento=$id_documento;
        $derivacion->nro_paginas_agregadas=$nro_paginas_agregadas;
        $derivacion->prioridad=$prioridad;
        $derivacion->plazo=$plazo;
        $derivacion->tipo="Original";
        $derivacion->recibido=0;
        $derivacion->fecha_recibido="NULL";
        $derivacion->derivado=0;
        $derivacion->fecha_derivado="NULL";
        $derivacion->gestion=$gestion;
        $derivacion->anulado=0;
        $derivacion->save();

    //Realizamos el registro de la derivacion
    /*$derivacion_creada = \DB::table('chasqui_derivaciones')->insert([
                              ['cite' => $cite,
                               'id_cargo_origen' => $id_cargo_origen,
                               'id_persona_origen' => $id_persona_origen,
                               'id_cargo_destino' => $id_cargo_destino,
                               'id_persona_destino' => $id_persona_destino,
                               'id_instruccion' => $id_instruccion,
                               'referencia' => $referencia,
                               'contenido' => $contenido,
                               'id_documento' => $id_documento,
                               'nro_paginas_agregadas' => $nro_paginas_agregadas,
                               'prioridad' => $prioridad,
                               'plazo' => $plazo,
                               'tipo' => 'Original',
                               'recibido' => 0,
                               'fecha_recibido' => 'NULL',
                               'derivado' => 0,
                               'fecha_derivado' => 'NULL',
                               'gestion' => $gestion,
                               'anulado' => 0]
                          ]);*/

    //Tomamos el id del registro realizado
    $id_derivacion_creada = $derivacion->id;

    //$id_derivacion_creada = 23;

    //Verficamos si se debe enviar el documento como copia
    if ($switch_copia == "on") {

      //Agregamos la palabra COPIA a la referencia
      $referencia_copia = $referencia." - COPIA";

      //Para cada usuario como copia
      for ($i=0;$i<count($id_usuarios_copia);$i++)
      {
        //Tomamos los datos de las personas a enviar como copia
        $id_persona_destino_copia = \DB::table('users')
            ->where('users.id', $id_usuarios_copia[$i])
            ->where('users.activo', 1)
            ->value('users.id_persona');

        $id_cargo_destino_copia = \DB::table('users')
            ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
            ->where('users.id', $id_usuarios_copia[$i])
            ->where('users.activo', 1)
            ->value('servidores_publicos.id_cargo');

        //Realizamos el registro de las copias, solo si es diferente al destinatario original
        if ($id_cargo_destino_copia != $id_usuario_destino && $id_persona_destino_copia != $id_persona_destino) {
          \DB::table('chasqui_derivaciones')->insert([
              ['cite' => $cite,
               'id_cargo_origen' => $id_cargo_origen,
               'id_persona_origen' => $id_persona_origen,
               'id_cargo_destino' => $id_cargo_destino_copia,
               'id_persona_destino' => $id_persona_destino_copia,
               'id_instruccion' => $id_instruccion,
               'instruccion_otro' => $instruccion_otro,
               'referencia' => $referencia_copia,
               'contenido' => $contenido,
               'id_documento' => $id_documento,
               'nro_paginas_agregadas' => $nro_paginas_agregadas,
               'prioridad' => $prioridad,
               'plazo' => $plazo,
               'tipo' => 'Copia',
               'recibido' => 0,
               'fecha_recibido' => 'NULL',
               'derivado' => 0,
               'fecha_derivado' => 'NULL',
               'gestion' => $gestion,
               'anulado' => 0]
          ]);
        }
      }
    }

    //Tomamos todos los campos para enviar a la vista bandeja de entrada
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";
    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();
    //Tomamos las derivaciones a mostrar
    $derivaciones = $this->derivaciones_recibidas();
    //Tomamos el plazo que se tiene para responder cada derivacion
    $array_plazo = $this->plazo_restante_derivaciones();
    //Establecemos el mensaje de exito a mostrar
    $mensaje_exito = "La correspondencia fue derivada correctamente.";

    //Establecemos las variables que definiran el mensaje de exito a mostrar y el id a imprimir en la funcion msj_exitoso_imprimir
    $accion_exitosa = "hoja_ruta_interna_enviada";
    $id_derivacion = $id_derivacion_creada;

    //Codificamos los parametros a pasar con get
    $accion_exitosa_codificado = base64_encode($accion_exitosa);
    $id_derivacion_codificado = base64_encode($id_derivacion);

    //return $this->vista_exitoso();
    return redirect('msj_exitoso_imprimir/'.$accion_exitosa_codificado.'/'.$id_derivacion_codificado);

    //Si todo salio bien, devolvemos 'ok' (cuando usabamos ajax)
    //return 'ok';
  }


  public function form_archivar_correspondencia($id_derivacion){
    //echo "ID: ".$id_derivacion;
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_area_logueado = $usuario->id_area;
      $id_persona_origen = $usuario->id_persona;
      $id_cargo_origen = $usuario->id_cargo;
      $id_area_origen = $usuario->id_area;

      $persona_origen = $usuario->nombres." ".$usuario->paterno." ".$usuario->materno;
      $cargo_origen = $usuario->descripcion_del_cargo;
      $sigla_origen = $usuario->sigla_del_area;
      $id_nivel_jerarquico_origen = $usuario->id_nivel_jerarquico;
      $unidad_origen = $usuario->unidad;
      $id_direccion_origen = $usuario->id_direccion;
      $id_entidad_origen = $usuario->id_entidad;
      $id_servidor_publico_origen = $usuario->id_servidor_publico;
    }


    //Tomamos las carpetas del area de la persona logueada
    $carpetas = \DB::table('chasqui_carpetas')
    ->select('id_carpeta', 'nombre')
    ->where('activo', 1)
    ->where('id_area', $id_area_logueado)
    ->get();

    //Tomamos el cite de la id_derivacion recibida
    $cite = \DB::table('chasqui_derivaciones')
    ->where('id_derivacion', $id_derivacion)
    ->value('cite');

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();
    $fecha = $hoy->format('Y-m-d');















    /*//Tomamos todos los posibles destinatarios segun regla general
    $destinatarios = $this->get_destinatarios();

    //Además del flujo normal, se tiene la tabla chasqui_flujos_especiales, donde se agregan flujos de derivacion especiales uno a uno, seleccionamos ese para el actual usuario
    $destinatarios_especiales = \DB::table('chasqui_flujos_especiales')
    ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_flujos_especiales.id_cargo_destino')
    ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
    ->join('servidores_publicos', 'servidores_publicos.id_cargo', '=', 'cargos.id_cargo')
    ->join('personas', 'personas.id_persona', '=', 'servidores_publicos.id_persona')
    ->join('users', 'users.id_persona', '=', 'personas.id_persona')
    ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo',
            'cargos.descripcion as descripcion_del_cargo', 'areas.sigla as sigla_del_area')
    ->where('chasqui_flujos_especiales.id_cargo_origen', $id_cargo_origen)
    ->where('chasqui_flujos_especiales.activo', 1)
    ->where('cargos.vigente', 1)
    ->where('servidores_publicos.activo', 1)
    ->where('users.activo', 1)
    ->orderby('cargos.id_cargo')
    ->get();

    //Tomamos el cite de la id_derivacion recibida
    $cite = \DB::table('chasqui_derivaciones')
    ->where('id_derivacion', $id_derivacion)
    ->value('cite');
    //->get();

    //$cite = $this->get_cite_comunicacion_interna();



    //Tomamos las posibles instrucciones
    $instrucciones = \DB::table('chasqui_instrucciones')
    ->where('activo', 1)
    ->select('*')
    ->get();

    //Establecemos que la accion a realizar sea derivar la correspondencia
    $accion = "derivar_correspondencia";
*/
    //Devolvemos la vista con los parametros necesarios
    return view("formularios_chasqui.form_archivar_correspondencia")
            ->with('carpetas', $carpetas)
            ->with('cite', $cite)
            ->with('fecha', $fecha)
            ->with("id_persona_origen", $id_persona_origen)
            ->with('id_area_origen', $id_area_origen)
            ->with('id_cargo_origen', $id_cargo_origen)
            ->with("persona_origen", $persona_origen)
            ->with('cargo_origen', $cargo_origen)
            ->with('sigla_origen', $sigla_origen)
            ->with('id_derivacion', $id_derivacion);
  }

  public function archivar_correspondencia(Request $request){
    //Tomamos los datos
    $id_area = $request->id_area;
    $id_derivacion = $request->id_derivacion;
    $id_cargo = $request->id_cargo;
    $id_persona = $request->id_persona;
    $cite = $request->cite;
    $id_carpeta = $request->id_carpeta;
    $proveido = $request->proveido;

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();

    //Establecemos en 1 el valor de derivado y la fecha actual en el registro de la tabla chasqui_derivaciones
    \DB::table('chasqui_derivaciones')
          ->where('id_derivacion', $id_derivacion)
          ->update(['derivado' => 1,
                    'fecha_derivado' => $hoy]);

    //Creamos el registro en la tabla chasqui_archivados
    \DB::table('chasqui_archivados')->insert([
        ['id_area' => $id_area,
         'id_cargo' => $id_cargo,
         'id_persona' => $id_persona,
         'id_carpeta' => $id_carpeta,
         'cite' => $cite,
         'proveido' => $proveido,
         'timestamp' => $hoy]
    ]);

    //Establecemos el valor de la variable accion_exitosa, la cual definira el mensaje de exito a mostrar en la funcion msj_exitoso
    $accion_exitosa = "correspondencia_archivada";
    $accion_exitosa_codificado = base64_encode($accion_exitosa);

    //Redireccionar a la ruta msj_exitoso
    return redirect('msj_exitoso/'.$accion_exitosa_codificado);
  }

  public function bandeja(){
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";

    //Tomamos las derivaciones a mostrar
    $derivaciones = $this->derivaciones_recibidas();

    //Tomamos el plazo que se tiene para responder cada derivacion
    $array_plazo = $this->plazo_restante_derivaciones();

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.bandeja_de_entrada")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder)
        ->with("derivaciones", $derivaciones)
        ->with("array_plazo", $array_plazo);
  }


  public function leer_correspondencia(Request $request){

    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();

    //Tomamos el valor de recibio de la derivacion actual
    $valor_recibido = \DB::table('chasqui_derivaciones')
                          ->where('id_derivacion', $request->id_derivacion)
                          ->value('recibido');

    //Si no recibio la correspondenciaantes, la marcamos la corresponda como leida y establecemos la fecha y hora actual
    if ($valor_recibido == 0) {
      \DB::table('chasqui_derivaciones')
            ->where('id_derivacion', $request->id_derivacion)
            ->update(['recibido' => 1,
                      'fecha_recibido' => $hoy]);
    }

    //Tomamos los datos de la derivacion
    $derivaciones = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
        ->where('chasqui_derivaciones.id_derivacion', $request->id_derivacion)
        ->select('chasqui_derivaciones.id_derivacion', 'chasqui_derivaciones.cite', 'chasqui_derivaciones.referencia', 'personas.nombres', 'personas.paterno', 'personas.materno',
                  'areas.sigla', 'cargos.descripcion as nombre_del_cargo', 'chasqui_instrucciones.descripcion as instruccion', 'chasqui_derivaciones.*')
        ->get();

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "";
    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Establecemos si las acciones de archivar, guardar o enviar estan habilitadas o no
    $acciones_habilitadas = $request->acciones_habilitadas;

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.leer_correspondencia")
        ->with("folder", $folder)
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("derivaciones", $derivaciones)
        ->with("acciones_habilitadas", $acciones_habilitadas);
  }


  public function guardar_copia_correspondencia(Request $request){
    //Tomamos la fecha actual
    $date = new Carbon();
    $hoy = Carbon::now();

    //Tomamos el valor de derivado de la derivacion actual
    $valor_derivado = \DB::table('chasqui_derivaciones')
                          ->where('id_derivacion', $request->id_derivacion)
                          ->value('derivado');

    //Para guardar una correspondencia recibida como copia, ponemos en 1 el campo derivado y establecemos la fecha actual
    if ($valor_derivado == 0) {
      \DB::table('chasqui_derivaciones')
            ->where('id_derivacion', $request->id_derivacion)
            ->update(['derivado' => 1,
                      'fecha_derivado' => $hoy]);
    }

    //Establecemos el valor de la variable accion_exitosa, la cual definira el mensaje de exito a mostrar en la funcion msj_exitoso
    $accion_exitosa = "copia_guardada";
    $accion_exitosa_codificado = base64_encode($accion_exitosa);

    //Redireccionar a la ruta msj_exitoso
    return redirect('msj_exitoso/'.$accion_exitosa_codificado);
  }

  public function msj_exitoso($accion_exitosa_codificado){

    //Decodificamos el parametro recibido
    $accion_exitosa = base64_decode($accion_exitosa_codificado);

    //Enfuncion a la accion exitosa, enviamos el mensaje que corresponda
    switch ($accion_exitosa) {
      case 'copia_guardada':
        $mensaje_exito = "La correspondencia recibida como copia, fue guardada.";
        break;

      case 'correspondencia_archivada':
          $mensaje_exito = "La correspondencia fue archivada correctamente.";
          break;

      default:
        // code...
        break;
    }

    //Tomamos todos los campos para enviar a la vista bandeja de entrada
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";
    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();
    //Tomamos las derivaciones a mostrar
    $derivaciones = $this->derivaciones_recibidas();
    //Tomamos el plazo que se tiene para responder cada derivacion
    $array_plazo = $this->plazo_restante_derivaciones();

    return view("chasqui.bandeja_de_entrada")
        ->with("folder", $folder)
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("derivaciones", $derivaciones)
        ->with("array_plazo", $array_plazo)
        ->with("mensaje_exito", $mensaje_exito);
  }


  //Funcion utilizada para redireccionar a la bandeja de entrada (evitando problema que se genera al recargar la pagina),
  //toma como parametros el codigo del mensaje a mostrar y el id de la derivacion a imprimir
  public function msj_exitoso_imprimir($accion_exitosa_codificado, $id_derivacion_codificado){

    //Decodificamos los parametros recibidos
    $accion_exitosa = base64_decode($accion_exitosa_codificado);
    $id_derivacion = base64_decode($id_derivacion_codificado);


    //Enfuncion a la accion exitosa, enviamos el mensaje que corresponda
    switch ($accion_exitosa) {
      case 'hoja_ruta_interna_enviada':
        $mensaje_exito = "La correspondencia fue enviada correctamente.";
        echo "<script>window.open('".route('pdf_hoja_ruta_interna', ['id_derivacion'=>$id_derivacion_codificado])."');</script>";
        break;

      case 'hoja_ruta_interna_derivada':
        $mensaje_exito = "La correspondencia fue derivada correctamente.";
        //echo "<script>window.open('".route('pdf_hoja_ruta_interna', ['id_derivacion'=>$id_derivacion_codificado])."');</script>";
        break;

      default:
        // code...
        break;
    }

    //Tomamos todos los campos para enviar a la vista bandeja de entrada
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "bandeja_de_entrada";
    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();
    //Tomamos las derivaciones a mostrar
    $derivaciones = $this->derivaciones_recibidas();
    //Tomamos el plazo que se tiene para responder cada derivacion
    $array_plazo = $this->plazo_restante_derivaciones();

    return view("chasqui.bandeja_de_entrada")
        ->with("folder", $folder)
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("derivaciones", $derivaciones)
        ->with("array_plazo", $array_plazo)
        ->with("mensaje_exito", $mensaje_exito);
  }


  public function copias_recibidas(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
    }

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "copias_recibidas";

    //Tomamos las derivaciones a mostrar
    $derivaciones = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
        ->where('chasqui_derivaciones.id_cargo_destino', $id_cargo_logueado)
        ->where('chasqui_derivaciones.tipo', 'Copia')
        ->where('chasqui_derivaciones.derivado', 1)
        ->where('chasqui_derivaciones.anulado', 0)
        ->select('chasqui_derivaciones.id_derivacion', 'personas.nombres', 'personas.paterno', 'personas.materno', 'areas.sigla', 'chasqui_instrucciones.descripcion as instruccion',
                  'chasqui_derivaciones.*')
        ->orderby('chasqui_derivaciones.id_derivacion', 'DESC')
        ->get();

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.copias_recibidas")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder)
        ->with("derivaciones", $derivaciones);
  }

  public function enviados(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
      $id_persona_logueado = $usuario->id_persona;
    }

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "enviados";

    $derivaciones = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->where('chasqui_derivaciones.id_cargo_origen', $id_cargo_logueado)
        ->where('chasqui_derivaciones.id_persona_origen', $id_persona_logueado)
        ->where('chasqui_derivaciones.anulado', 0)
        ->select('chasqui_derivaciones.id_derivacion', 'personas.nombres', 'personas.paterno', 'personas.materno', 'areas.sigla',
                  'chasqui_derivaciones.*')
        ->orderby('chasqui_derivaciones.id_derivacion', 'DESC')
        ->get();

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.enviados")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder)
        ->with("derivaciones", $derivaciones);
  }

  public function archivados(){
    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_area = $usuario->id_area;

      $id_cargo_logueado = $usuario->id_cargo;
      $id_persona_logueado = $usuario->id_persona;
    }

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "archivados";

    //Tomamos las carpetas a mostrar
    $carpetas = \DB::table('chasqui_carpetas')
        ->where('id_area', $id_area)
        ->where('activo', 1)
        ->select('id_carpeta', 'nombre')
        ->get();

    //Tomamos las derivaciones archivadas
    $archivados = \DB::table('chasqui_archivados')
        ->where('id_area', $id_area)
        ->select('id_archivado', 'id_carpeta', 'cite', 'proveido')
        ->orderby('id_archivado', 'DESC')
        ->get();

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.archivados")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder)
        ->with("carpetas", $carpetas)
        ->with("archivados", $archivados);
  }

  //Funcion para imprimir comunicacion interna (eligiendo posicion)
  public function reimprimir_comunicacion_interna($id_derivacion_codificado, $posicion){
    //Generamos el pdf en otra ventana
    echo "<script>window.open('".route('pdf_reimprimir_comunicacion_interna', ['id_derivacion'=>$id_derivacion_codificado, 'posicion'=>$posicion])."');</script>";

    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_cargo_logueado = $usuario->id_cargo;
      $id_persona_logueado = $usuario->id_persona;
    }

    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "enviados";

    $derivaciones = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->where('chasqui_derivaciones.id_cargo_origen', $id_cargo_logueado)
        ->where('chasqui_derivaciones.id_persona_origen', $id_persona_logueado)
        ->where('chasqui_derivaciones.anulado', 0)
        ->select('chasqui_derivaciones.id_derivacion', 'personas.nombres', 'personas.paterno', 'personas.materno', 'areas.sigla',
                  'chasqui_derivaciones.*')
        ->orderby('chasqui_derivaciones.id_derivacion', 'DESC')
        ->get();

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.enviados")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder)
        ->with("derivaciones", $derivaciones);

  }


  //Funcion para imprimir comunicacion interna (todo el flujo), tomando como identificador el cite
  public function imprimir_comunicacion_interna_todo($cite_codificado, $ruta_vuelta_codificado){
    //Generamos el pdf en otra ventana
    echo "<script>window.open('".route('pdf_imprimir_comunicacion_interna_todo', ['cite_codificado'=>$cite_codificado])."');</script>";

    //Tomamos los datos de la persona logueada
    $usuario_logueado =  $this->datos_persona_logueada();
    foreach ($usuario_logueado as $usuario) {
      $id_area = $usuario->id_area;

      $id_cargo_logueado = $usuario->id_cargo;
      $id_persona_logueado = $usuario->id_persona;
    }

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Tomamos la ruta donde enviar tras generar el pdf
    $ruta_vuelta = base64_decode($ruta_vuelta_codificado);

    if ($ruta_vuelta == "archivados") {
      //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
      $folder = "archivados";

      //Tomamos las carpetas a mostrar
      $carpetas = \DB::table('chasqui_carpetas')
          ->where('id_area', $id_area)
          ->where('activo', 1)
          ->select('id_carpeta', 'nombre')
          ->get();

      //Tomamos las derivaciones archivadas
      $archivados = \DB::table('chasqui_archivados')
          ->where('id_area', $id_area)
          ->select('id_archivado', 'id_carpeta', 'cite', 'proveido')
          ->orderby('id_archivado', 'DESC')
          ->get();

      //Devolvemos la vista con los parametros necesarios
      return view("chasqui.archivados")
          ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
          ->with("folder", $folder)
          ->with("carpetas", $carpetas)
          ->with("archivados", $archivados);
    }
    elseif ($ruta_vuelta == "seguimiento") {
      //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
      $folder = "seguimiento";

      //return redirect()->back();
      //Devolvemos la vista con los parametros necesarios
      return view("chasqui.seguimiento_buscar")
          ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
          ->with("folder", $folder);
    }
  }

  public function seguimiento_buscar(){
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "seguimiento";

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.seguimiento_buscar")
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder);
  }

  public function seguimiento(Request $request){
    //Tomamos los cites que cumplan con algun criterio de busqueda
    $cites = \DB::table('chasqui_derivaciones')
        ->where('cite', $request->buscar)
        ->orwhere('cite', 'like', '%'.$request->buscar.'%')
        ->orwhere('referencia', 'like', '%'.$request->buscar.'%')
        ->orwhere('contenido', 'like', '%'.$request->buscar.'%')
        ->select('cite')
        ->distinct()
        ->get();

    //Tomamos los valores para mostrar en el menu
    //Enviamos la variable folder para marcar en azul el folder en el que se encuentra
    $folder = "seguimiento";

    //Tomamos la cantidad de correspondencia nueva (sin recepcionar)
    $bandeja_nuevos_cantidad = $this->bandeja_nuevos_cantidad();

    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.seguimiento")
        ->with("cites", $cites)
        ->with("bandeja_nuevos_cantidad", $bandeja_nuevos_cantidad)
        ->with("folder", $folder);
  }

  public function manual(){
    //Devolvemos la vista con los parametros necesarios
    return view("chasqui.manual");
  }















  //Funcion interna para encriptar datos (para metodos get)
  public function encriptar ($texto_plano){
    //$algorithm = MCRYPT_BLOWFISH;
  /*  $algorithm = "MCRYPT_RIJNDAEL_128";
    $key = 'That golden key that opens the palace of eternity.';
    $data = $texto_plano;
    $mode = "MCRYPT_MODE_CBC";

    $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);

    $encrypted_data = mcrypt_encrypt($algorithm, $key, $data, $mode, $iv);
    //return $texto_cifrado = base64_encode($encrypted_data);*/
    return $texto_cifrado = base64_encode($texto_plano);

    //$plain_text = base64_encode($encrypted_data);
    //echo $plain_text . "\n";
  }

  //Funcion interna para desencriptar datos (para metodos get)
  public function desencriptar ($texto_cifrado){

    return $decoded = base64_decode($texto_cifrado);
    //$algorithm = MCRYPT_BLOWFISH;
  /*  $algorithm = "MCRYPT_RIJNDAEL_128";
    $key = 'That golden key that opens the palace of eternity.';
    $data = $texto_cifrado;
    $mode = "MCRYPT_MODE_CBC";

    $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);

    $encrypted_data = base64_decode($data);
    return $texto_plano = mcrypt_decrypt($algorithm, $key, $encrypted_data, $mode, $iv);
    //echo $decoded . "\n";

    /*$encrypted_data = mcrypt_encrypt($algorithm, $key, $data, $mode, $iv);
    $plain_text = base64_encode($encrypted_data);
    echo $plain_text . "\n";

    $encrypted_data = base64_decode($plain_text);
    $decoded = mcrypt_decrypt($algorithm, $key, $encrypted_data, $mode, $iv);
    echo $decoded . "\n";*/
  }
}
