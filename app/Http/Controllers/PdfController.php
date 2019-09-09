<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use DateTime;
use Fpdf;

use App\User;
use App\Usada;
use App\Vacacion;
use App\Ministerio;
use App\Direccion;
use App\Unidad;
use App\Suspension;

class PdfController extends Controller
{
  //Funcion para imprimir la hoja de ruta interna una vez generada
  public function pdf_hoja_ruta_interna($id_derivacion_codificado) {
    //Decodificamos el parametro obtenido
    $id_derivacion = base64_decode($id_derivacion_codificado);

     //Dado el id_derivacion, obtenemos el cite
     $cite = \DB::table('chasqui_derivaciones')
                 ->where('id_derivacion', $id_derivacion)
                 ->value('cite');

    //Dado el id_derivacion, obtenemos los datos de la derivacion y del remitente
    $derivaciones_origen = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
        ->where('chasqui_derivaciones.id_derivacion', $id_derivacion)
        ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla',
                 'chasqui_instrucciones.id_instruccion', 'chasqui_instrucciones.descripcion as instruccion', 'chasqui_derivaciones.*')
        ->get();

    foreach ($derivaciones_origen as $derivacion) {
      //Pasamos los valores necesarios
      $remitente = $derivacion->nombres." ".$derivacion->paterno." ".$derivacion->materno." - ".$derivacion->nombre_del_cargo." - ".$derivacion->sigla;
      $cite = $derivacion->cite;
      $id_instruccion = $derivacion->id_instruccion;
      $instruccion = $derivacion->instruccion;
      $instruccion_otro = $derivacion->instruccion_otro;
      $referencia = $derivacion->referencia;
      $nro_caracteres_referencia = strlen($referencia);
      $contenido = $derivacion->contenido;
      $nro_caracteres_contenido = strlen($contenido);
      $nro_paginas_agregadas = $derivacion->nro_paginas_agregadas;
      $prioridad = $derivacion->prioridad;
      $plazo = $derivacion->plazo;
      $fecha_creacion = strstr($derivacion->fecha_creacion, ' ', true);
      $hora_creacion = substr($derivacion->fecha_creacion, -8);
    }

    //Dado el id_derivacion, obtenemos los datos del destinatario
    $derivaciones_destino = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->where('chasqui_derivaciones.id_derivacion', $id_derivacion)
        ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla')
        ->get();

    foreach ($derivaciones_destino as $derivacion_d) {
      //Pasamos los valores necesarios
      $destinatario = $derivacion_d->nombres." ".$derivacion_d->paterno." ".$derivacion_d->materno." - ".$derivacion_d->nombre_del_cargo." - ".$derivacion_d->sigla;
    }

    //Dado el cite de la derivacion, obtenemos el id_derivacion de todas las derivaciones enviadas como copia
    $referencia_copias = $referencia." - COPIA";
    $derivaciones_copias = \DB::table('chasqui_derivaciones')
        ->where('referencia', $referencia_copias)
        ->select('id_derivacion')
        ->get();

    //Establecemos los destinatarios como copia
    $destinatarios_copia = array();
    $nro_destinatarios_copia = 0;
    foreach ($derivaciones_copias as $derivacion_copia) {
      $destinatario_copia = \DB::table('chasqui_derivaciones')
          ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
          ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->where('chasqui_derivaciones.id_derivacion', $derivacion_copia->id_derivacion)
          ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla')
          ->get();
      foreach ($destinatario_copia as $dest_copia) {
        $destinatarios_copia[] = $dest_copia->nombres." ".$dest_copia->paterno." ".$dest_copia->materno." - ".$dest_copia->nombre_del_cargo." - ".$dest_copia->sigla;
      }

      //Incrementamos en 1 el nro de destinatarios como copia
      $nro_destinatarios_copia = $nro_destinatarios_copia + 1;
    }

    //$destinatarios_copia[] = "Antonia Wilma Alanoca Mamani - MINISTRA DE CULTURAS Y TURISMO	- MDCyT";

    //Establecemos el tamaño del texto a imprimir en funcion a la cantidad de lineas de referencia, contenido y destinatarios como copia
    //Cada linea es aproximadamente 100 caracteres, por lo que para ver si se tienen mas de 8 lineas realizamos la siguiente operacion
    $cantidad_de_lineas_dinamicas = $nro_caracteres_contenido + $nro_caracteres_referencia + $nro_destinatarios_copia*100;
    //$cantidad_de_lineas_dinamicas = 2200;
    if ($cantidad_de_lineas_dinamicas <= 1200) {$tamaño_texto = "100";}
    elseif ($cantidad_de_lineas_dinamicas <= 1500) {$tamaño_texto = "90";}
    elseif ($cantidad_de_lineas_dinamicas <= 2000) {$tamaño_texto = "80";}
    else {$tamaño_texto = "70";}

    //Establecemos los valores a pasar a la vista
    $datos = array('cite' => $cite,
                   'remitente' => $remitente,
                   'destinatario' => $destinatario,
                   'id_instruccion' => $id_instruccion,
                   'instruccion' => $instruccion,
                   'instruccion_otro' => $instruccion_otro,
                   'referencia' => $referencia,
                   'contenido' => $contenido,
                   'nro_paginas_agregadas' => $nro_paginas_agregadas,
                   'prioridad' => $prioridad,
                   'plazo' => $plazo,
                   'fecha_creacion' => $fecha_creacion,
                   'hora_creacion' => $hora_creacion,
                   'tamaño_texto' => $tamaño_texto);

     //Establecemos las instrucciones
     $instrucciones = array();
     $suma_instrucciones = 0;
     $instrucciones_consulta = \DB::table('chasqui_instrucciones')
         ->where('activo', 1)
         ->where('id_instruccion', '!=', 999999)
         ->select('descripcion')
         ->get();
     foreach ($instrucciones_consulta as $instruccion_conuslta) {
       $instrucciones[] = $instruccion_conuslta->descripcion;
       $suma_instrucciones = $suma_instrucciones+1;
     }


    $pdf = \PDF::loadView('pdfs_chasqui.pdf_hoja_ruta_interna', compact('datos', 'destinatarios_copia', 'instrucciones', 'suma_instrucciones'))
                     ->setPaper('letter')
                     ->stream('github.pdf');

    //Dado el valor de la posicion del texto en la hoja, generamos el reporte arriba o abajo
    /*if ($posicion == "superior") {
      $pdf = \PDF::loadView('pdfs_chasqui.pdf_comunicacion_interna_posicion_1', compact('datos'), compact('destinatarios_copia'))
                      ->setPaper('letter')
                      ->stream('github.pdf');
    }
    elseif ($posicion == "inferior") {
      $pdf = \PDF::loadView('pdfs_chasqui.pdf_comunicacion_interna_posicion_2', compact('datos'), compact('destinatarios_copia'))
                      ->setPaper('letter')
                      ->stream('github.pdf');
    }*/
    return $pdf;







    //Utilizamos la funcion para generar el pdf, pasamos el id_derivacion_codificado y la posicion en la hoja (superior o inferior)
    $pdf = $this->generar_pdf_comunicacion_interna_una_derivacion($id_derivacion_codificado, $posicion_en_la_hoja);
    return $pdf;
  }






  //Funcion para imprimir la comunicacion interna una vez generada
  public function pdf_comunicacion_interna($id_derivacion_codificado) {
    //Decodificamos el parametro obtenido
    $id_derivacion = base64_decode($id_derivacion_codificado);

     //Dado el id_derivacion, obtenemos el cite
     $cite = \DB::table('chasqui_derivaciones')
                 ->where('id_derivacion', $id_derivacion)
                 ->value('cite');

    //Obtenemos la posicion del registro (par o impar), para ver si se imprimira en la posicion 1(parte superiror de la hoja) o 2(inferior de la hoja)
    // Inicializamos la variable auxiliar @rownum
    \DB::statement(\DB::raw('SET @rownum = 0'));

    //Tomamos todos los registros con el cite dado y los numeramos con la variable auxiliar rownum
    $derivaciones_numeradas = \DB::table('chasqui_derivaciones')
    ->select(\DB::raw('@rownum := @rownum + 1 as rownum'), 'id_derivacion')
    ->where('cite', $cite)
    ->where('tipo', 'Original')
    ->where('anulado', 0)
    ->get();
    //dd($derivaciones_numeradas);

    foreach ($derivaciones_numeradas as $derivacion_numerada) {
      //Si el id_derivacion es el actual, tomamos su posicion (el valor de rownum)
      if ($derivacion_numerada->id_derivacion == $id_derivacion) {
        $nro_registro = $derivacion_numerada->rownum;
      }
    }

    //Una vez obtenido el numero de registro, vemos si es par o impar, para determinar la posicion de la hoja en la que se imprimira
    if ($nro_registro%2 == 0) {
      //Es impar, entonces se imiprime en la parte inferior
      $posicion_en_la_hoja = "inferior";
    }
    else {
      //Es par, entonces se imiprime en la parte inferior
      $posicion_en_la_hoja = "superior";
    }

    //Utilizamos la funcion para generar el pdf, pasamos el id_derivacion_codificado y la posicion en la hoja (superior o inferior)
    $pdf = $this->generar_pdf_comunicacion_interna_una_derivacion($id_derivacion_codificado, $posicion_en_la_hoja);
    return $pdf;
  }

  //Funcion para reimprimir la derivacion
  public function pdf_reimprimir_comunicacion_interna($id_derivacion_codificado, $posicion){
    //Imprimimos arriba o abajo segun lo establecido
    $pdf = $this->generar_pdf_comunicacion_interna_una_derivacion($id_derivacion_codificado, $posicion);
    return $pdf;
  }


  //Funcion interna para generar el pdf de la comunicacion interna
  public function generar_pdf_comunicacion_interna_una_derivacion($id_derivacion_codificado, $posicion) {
    //Decodificamos el parametro obtenido
    $id_derivacion = base64_decode($id_derivacion_codificado);

    //Dado el id_derivacion, obtenemos los datos de la derivacion y del remitente
    $derivaciones_origen = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
        ->where('chasqui_derivaciones.id_derivacion', $id_derivacion)
        ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla',
                 'chasqui_instrucciones.descripcion as instruccion', 'chasqui_derivaciones.*')
        ->get();

    foreach ($derivaciones_origen as $derivacion) {
      //Pasamos los valores necesarios
      $remitente = $derivacion->nombres." ".$derivacion->paterno." ".$derivacion->materno." - ".$derivacion->nombre_del_cargo." - ".$derivacion->sigla;
      $cite = $derivacion->cite;
      $instruccion = $derivacion->instruccion;
      $referencia = $derivacion->referencia;
      $nro_caracteres_referencia = strlen($referencia);
      $contenido = $derivacion->contenido;
      $nro_caracteres_contenido = strlen($contenido);
      $nro_paginas_agregadas = $derivacion->nro_paginas_agregadas;
      $prioridad = $derivacion->prioridad;
      $plazo = $derivacion->plazo;
      $fecha_creacion = strstr($derivacion->fecha_creacion, ' ', true);
      $hora_creacion = substr($derivacion->fecha_creacion, -8);
    }

    //Dado el id_derivacion, obtenemos los datos del destinatario
    $derivaciones_destino = \DB::table('chasqui_derivaciones')
        ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
        ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
        ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
        ->where('chasqui_derivaciones.id_derivacion', $id_derivacion)
        ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla')
        ->get();

    foreach ($derivaciones_destino as $derivacion_d) {
      //Pasamos los valores necesarios
      $destinatario = $derivacion_d->nombres." ".$derivacion_d->paterno." ".$derivacion_d->materno." - ".$derivacion_d->nombre_del_cargo." - ".$derivacion_d->sigla;
    }

    //Dado el cite de la derivacion, obtenemos el id_derivacion de todas las derivaciones enviadas como copia
    $referencia_copias = $referencia." - COPIA";
    $derivaciones_copias = \DB::table('chasqui_derivaciones')
        ->where('referencia', $referencia_copias)
        ->select('id_derivacion')
        ->get();

    //Establecemos los destinatarios como copia
    $destinatarios_copia = array();
    $nro_destinatarios_copia = 0;
    foreach ($derivaciones_copias as $derivacion_copia) {
      $destinatario_copia = \DB::table('chasqui_derivaciones')
          ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
          ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
          ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
          ->where('chasqui_derivaciones.id_derivacion', $derivacion_copia->id_derivacion)
          ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla')
          ->get();
      foreach ($destinatario_copia as $dest_copia) {
        $destinatarios_copia[] = $dest_copia->nombres." ".$dest_copia->paterno." ".$dest_copia->materno." - ".$dest_copia->nombre_del_cargo." - ".$dest_copia->sigla;
      }

      //Incrementamos en 1 el nro de destinatarios como copia
      $nro_destinatarios_copia = $nro_destinatarios_copia + 1;
    }

    //$destinatarios_copia[] = "Antonia Wilma Alanoca Mamani - MINISTRA DE CULTURAS Y TURISMO	- MDCyT";

    //Establecemos el tamaño del texto a imprimir en funcion a la cantidad de lineas de referencia, contenido y destinatarios como copia
    //Cada linea es aproximadamente 100 caracteres, por lo que para ver si se tienen mas de 8 lineas realizamos la siguiente operacion
    $cantidad_de_lineas_dinamicas = $nro_caracteres_contenido + $nro_caracteres_referencia + $nro_destinatarios_copia*100;
    //$cantidad_de_lineas_dinamicas = 2200;
    if ($cantidad_de_lineas_dinamicas <= 1200) {$tamaño_texto = "100";}
    elseif ($cantidad_de_lineas_dinamicas <= 1500) {$tamaño_texto = "90";}
    elseif ($cantidad_de_lineas_dinamicas <= 2000) {$tamaño_texto = "80";}
    else {$tamaño_texto = "70";}

    //Establecemos los valores a pasar a la vista
    $datos = array('cite' => $cite,
                   'remitente' => $remitente,
                   'destinatario' => $destinatario,
                   'instruccion' => $instruccion,
                   'referencia' => $referencia,
                   'contenido' => $contenido,
                   'nro_paginas_agregadas' => $nro_paginas_agregadas,
                   'prioridad' => $prioridad,
                   'plazo' => $plazo,
                   'fecha_creacion' => $fecha_creacion,
                   'hora_creacion' => $hora_creacion,
                   'tamaño_texto' => $tamaño_texto);

    //Dado el valor de la posicion del texto en la hoja, generamos el reporte arriba o abajo
    if ($posicion == "superior") {
      $pdf = \PDF::loadView('pdfs_chasqui.pdf_comunicacion_interna_posicion_1', compact('datos'), compact('destinatarios_copia'))
                      ->setPaper('letter')
                      ->stream('github.pdf');
    }
    elseif ($posicion == "inferior") {
      $pdf = \PDF::loadView('pdfs_chasqui.pdf_comunicacion_interna_posicion_2', compact('datos'), compact('destinatarios_copia'))
                      ->setPaper('letter')
                      ->stream('github.pdf');
    }
    return $pdf;
  }

  //Funcion para reimprimir la derivacion
  public function pdf_imprimir_comunicacion_interna_todo($cite_codificado){
    //Imprimimos arriba o abajo segun lo establecido
    $pdf = $this->generar_pdf_comunicacion_interna_todo($cite_codificado);
    return $pdf;
  }

  //Funcion interna para generar el pdf de la comunicacion interna (todo el flujo)
  public function generar_pdf_comunicacion_interna_todo($cite_codificado) {
    //Decodificamos el parametro obtenido
    $cite = base64_decode($cite_codificado);

   //Establecemos los valores a pasar a la vista
   $datos = array('cite' => $cite,
                  'tamaño_texto' => "100");

   //Dado el cite, obtenemos los datos de la derivacion y del remitente
   $derivaciones = \DB::table('chasqui_derivaciones')
       ->join('personas as personas_origen', 'personas_origen.id_persona', '=', 'chasqui_derivaciones.id_persona_origen')
       ->join('cargos as cargos_origen', 'cargos_origen.id_cargo', '=', 'chasqui_derivaciones.id_cargo_origen')
       ->join('areas as areas_origen', 'areas_origen.id_area', '=', 'cargos_origen.id_area')
       ->join('personas as personas_destino', 'personas_destino.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
       ->join('cargos as cargos_destino', 'cargos_destino.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
       ->join('areas as areas_destino', 'areas_destino.id_area', '=', 'cargos_destino.id_area')
       ->join('chasqui_instrucciones', 'chasqui_instrucciones.id_instruccion', '=', 'chasqui_derivaciones.id_instruccion')
       ->where('chasqui_derivaciones.cite', $cite)
       ->where('chasqui_derivaciones.tipo', 'Original')
       ->where('chasqui_derivaciones.anulado', 0)
       ->select('personas_origen.nombres as nombres_origen', 'personas_origen.paterno as paterno_origen', 'personas_origen.materno as materno_origen', 'cargos_origen.descripcion as nombre_del_cargo_origen', 'areas_origen.sigla as sigla_origen',
                'personas_destino.nombres as nombres_destino', 'personas_destino.paterno as paterno_destino', 'personas_destino.materno as materno_destino', 'cargos_destino.descripcion as nombre_del_cargo_destino', 'areas_destino.sigla as sigla_destino',
                'chasqui_instrucciones.descripcion as instruccion', 'chasqui_derivaciones.*')
       ->get();

   //Tomamos todas las derivaciones como copia
   $derivaciones_copia = \DB::table('chasqui_derivaciones')
       ->join('personas', 'personas.id_persona', '=', 'chasqui_derivaciones.id_persona_destino')
       ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_derivaciones.id_cargo_destino')
       ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
       ->where('chasqui_derivaciones.cite', $cite)
       ->where('chasqui_derivaciones.tipo', 'Copia')
       ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla', 'chasqui_derivaciones.referencia')
       ->get();

   //Dado el cite, obtenemos los datos en caso que se haya archivado el documento
   $archivados = \DB::table('chasqui_archivados')
       ->join('chasqui_carpetas', 'chasqui_carpetas.id_carpeta', '=', 'chasqui_archivados.id_carpeta')
       ->join('personas', 'personas.id_persona', '=', 'chasqui_archivados.id_persona')
       ->join('cargos', 'cargos.id_cargo', '=', 'chasqui_archivados.id_cargo')
       ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
       ->where('chasqui_archivados.cite', $cite)
       ->select('personas.nombres', 'personas.paterno', 'personas.materno', 'cargos.descripcion as nombre_del_cargo', 'areas.sigla', 'chasqui_carpetas.nombre as nombre_de_carpeta',
                'chasqui_archivados.proveido', 'chasqui_archivados.timestamp')
       ->get();

    $pdf = \PDF::loadView('pdfs_chasqui.pdf_comunicacion_interna_todo', compact('derivaciones', 'derivaciones_copia', 'datos', 'archivados'))
                    ->setPaper('letter')
                    ->stream('github.pdf');
    return $pdf;
  }





























  public function pdf_comunicacion_interna_b($id_derivacion_codificado) {

      //Decodificamos el parametro obtenido
      $id_derivacion = base64_decode($id_derivacion_codificado);

      //Obtenemos la posicion del registro (par o impar), para ver si se imprimira en la posicion 1(parte superiror de la hoja) o 2(inferior de la hoja)
      //Dado el id_derivacion, obtenemos el cite
      $cite = \DB::table('chasqui_derivaciones')
                  ->where('id_derivacion', $id_derivacion)
                  ->value('cite');

      // Inicializamos la variable auxiliar @rownum
      \DB::statement(\DB::raw('SET @rownum = 0'));

      //Tomamos todos los registros con el cite dado y los numeramos con la variable auxiliar rownum
      $derivaciones_numeradas = \DB::table('chasqui_derivaciones')
      ->select(\DB::raw('@rownum := @rownum + 1 as rownum'), 'id_derivacion')
      ->where('cite', $cite)
      ->where('tipo', 'Original')
      ->where('anulado', 0)
      ->get();
      //dd($derivaciones_numeradas);

      foreach ($derivaciones_numeradas as $derivacion_numerada) {
        //Si el id_derivacion es el actual, tomamos su posicion (el valor de rownum)
        if ($derivacion_numerada->id_derivacion == $id_derivacion) {
          $nro_registro = $derivacion_numerada->rownum;
        }
      }

      //Una vez obtenido el numero de registro, vemos si es par o impar, para determinar la posicion de la hoja en la que se imprimira
      if ($nro_registro/2 == 0) {
        //Es par, entonces se imiprime en la parte inferior
        $posicion_en_la_hoja = "inferior";
      }
      else {
        //Es par, entonces se imiprime en la parte inferior
        $posicion_en_la_hoja = "superior";
      }




      //Dado el valor de la posicion del texto en la hoja, generamos el reporte arriba o abajo
      if ($posicion_en_la_hoja == "superior") {

      }
      else {

      }


      //echo "hola";
      /*$suspension = Suspension::find($id_suspension);
      $id_solicitud = $suspension->id_vacacion;

      $hoy = new DateTime(date('Y-m-d'));

      $solicitud = Vacacion::find($suspension->id_vacacion);//Solicitud suspension

      $user_id = $suspension->id_usuario;
      $usuario=User::find($user_id);
      $personal = \DB::table('personal')
      ->join('users', 'personal.cedula', '=', 'users.ci')
      ->join('cargos', 'personal.id_cargo', '=', 'cargos.num_item')
      ->join('areas', 'personal.idarea', '=', 'areas.idarea')
      ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
      ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
      ->where('cedula', $usuario->ci)
      ->select('personal.*', 'users.*', 'users.id as id_usuario', 'cargos.*', 'areas.*',
      'unidades.nombre as unidad',
      'direcciones.nombre as direccion')->get();

      $total = Usada::where('id_usuario', $user_id)
      ->where('id_solicitud', $id_solicitud)
      ->whereBetween('id_estado', [9, 11])
      ->select(\DB::raw('SUM(usadas.usadas) as total'))
      ->orderBy('title', 'asc')->get();

      $cas = \DB::table('gestiones')
      ->where('gestiones.vigencia', '>', $hoy)
      ->where('id_usuario', $user_id)
      ->select('year', 'month', 'day')
      ->orderBy('id', 'desc')
      ->first();

      $disponibles = \DB::table('users')
      ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
      ->where('users.id', $user_id)
      ->where('gestiones.vigencia', '>', $hoy)
      ->select(\DB::raw('SUM(gestiones.saldo) as saldo'))
      // ->orderBy('gestiones.id', 'asc')
      ->get();

      $usadas = \DB::table('usadas')
      ->select('usadas.*', \DB::raw("group_concat(start SEPARATOR ', ') as inicio, count(start) as numero"))
      ->where('id_solicitud', $id_solicitud)
      ->whereBetween('id_estado', [9, 11])
      ->groupBy('title')
      // ->orderBy('usadas.tiempo')
      ->orderBy('numero', 'desc')
      ->get();

      $hoy = new DateTime(date('Y-m-d'));
      $alta = new DateTime($personal[0]->fechaingreso);

      if($personal[0]->fechabaja == null){//con baja
        $antiguedad = $alta->diff($hoy);
      }
      else {

        $baja = new DateTime($personal[0]->fechabaja);
        $antiguedad = $alta->diff($baja);
      }

      $a = $antiguedad->y. 'a';
      $m = $antiguedad->m. 'm';
      $d = $antiguedad->d. 'd';

      //Functions
      $n = 3;
      if(count($usadas) > 0 ){
          if(count($usadas) == 2){
              $n = 4;
          }
          if(count($usadas) == 3){
              $n = 3;
          }
      }*/

      $pdf = new Fpdf();
      $pdf::AddPage();

      //CABECERA
      $pdf::Image('img/logochacana.jpg' , 15 ,10, 40 , 10,'JPG');
      $pdf::Image('img/chasqui_diseño_color.png' , 160 ,10, 40 , 10,'PNG');

      $pdf::Ln();
      $pdf::SetFont('Arial', 'B', 12);
      $pdf::Cell(55, 2, '', 0);
      $pdf::MultiCell(90,4.5,nl2br('COMUNICACIÓN INTERNA dfasdfasdfasdfasdfasdfasdf sdf asdf asdfa'),0, 'C');
      $pdf::cell(100,10,' _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _',0);
      $pdf::Ln();
      $pdf::Ln();
      $pdf::Ln();
      //$pdf::cell(100,10,' _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _',0);

      /*$pdf::Cell(45, 5, '', 0);
      $pdf::Cell(100, 5, '', 0,0,'C', 0);
      $pdf::SetFont('Arial', 'B', 14);
      $pdf::Cell(45, 5, '', 0,0,'R', 0);
      // $pdf::Cell(45, 5, 'V-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / S-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT), 0,0,'R', 0);
      $pdf::Ln();
      $pdf::Cell(45, 2, '', 0);
      $pdf::SetFont('Arial', 'B', 18);
      //$pdf::Cell(100, 2, 'SOLICITUD DE SUSPENSIÓN', 0,0,'C', 0);
      $pdf:: MultiCell(0,4.5,nl2br('SOLICITUD DE SUSPENSIÓNdfasdfasdfasdfasdfasdfasdf sdf asdf asdfa'),0);
      $pdf:: MultiCell(0,4.5,nl2br('SOLICITUD DE SUSPENSIÓNdfasdfasdfasdfasdfasdfasdf sdf asdf asdfa'),0);
      $pdf:: MultiCell(0,4.5,nl2br('SOLICITUD DE SUSPENSIÓNdfasdfasdfasdfasdfasdfasdf sdf asdf asdfa'),0);
      $pdf:: MultiCell(0,4.5,nl2br('SOLICITUD DE SUSPENSIÓNdfasdfasdfasdfasdfasdfasdf sdf asdf asdfa'),0);
      $pdf::Cell(45, 2, '', 0);
      $pdf::Ln();
      $pdf::Cell(45, 2, '', 0);
      $pdf::Cell(100, 4, '', 0,0,'C',0);
      //$pdf::Image('img/logo_jaqi.png' , 170 ,10, 30 , 6,'PNG');
      $pdf::SetFont('Arial', 'B', 12);
      /*$pdf::Cell(45, 4, 'S-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / V-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

      //DATOS PERSONALES
      $pdf::Ln(8);
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Nombre:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->fechaingreso,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'CAS:','LB',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
      $pdf::Ln(7);

      //DIAS DE VACACION
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','',11);
      $pdf::cell(32,6,'Disponibles','LRB',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(32,6,($disponibles[0]->saldo),'LRB',0,'C');
      $pdf::SetFont('Arial','',11);
      $pdf::cell(32,6,'Suspendidas','BR',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(32,6,$total[0]->total,'B',0,'C');
      $pdf::SetFont('Arial','',11);
      $pdf::cell(31,6,'Saldo','LRB',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(31,6,($disponibles[0]->saldo + $total[0]->total),'LRB',0,'C');
      $pdf::Ln(9);

      //DETALLE DE DIAS SOLICITADOS
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DETALLE DE DÍAS DE SUSPENSIÓN SOLICITADOS',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','B',10);
      $h = round(190/count($usadas));
      foreach ($usadas as $i => $item) {
          if($i == count($usadas)-1){
              $pdf::cell(0,6,$item->title,1,"","L");
          }
          else{
              $pdf::cell($h,6,$item->title,1,"","L");
          }
      }
      $pdf::Ln();
      $pdf::SetFont('Arial','',9);

          $x = $pdf::GetX();
          $y = $pdf::GetY();
          $push_right = 0;
          $x = $pdf::GetX();
          $y = $pdf::GetY();
          $push_right = 0;
          if (count($usadas) > 0){
          $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
          $push_right += $w;
          $pdf::SetXY($x + $push_right, $y);
          }
          if (count($usadas) > 1){
          $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
          // $pdf::MultiCell($w = 63,$y-$y1,f_formato_array($item->inicio),1,'L',0);
          $push_right += $w;
          $pdf::SetXY($x + $push_right, $y);
          // $pdf::MultiCell(0,$y-$y1,"",1,'L',0);
          }
          if (count($usadas) > 2){
          $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
          }
      $pdf::Ln(9);

      $pdf::SetFont('Arial','B',10);
      $pdf::cell(24,10,'Observación:','LTB',"","L");
      $pdf::SetFont('Arial','',10);
      $pdf::cell(0,10,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
      $pdf::Ln();
      $pdf::SetXY($pdf::GetX(), 127);
      $pdf::Ln();
      $pdf::SetFont('Arial','',10);
      $pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
      $pdf::cell(63,5,'','',0,'C');
      $pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');
      $pdf::Ln();
      $pdf::cell(0,5,'- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ',0,'','C');


      //CABECERA
      $pdf::Ln(9);
      $pdf::Image('img/logochacana.jpg' , 10 ,151, 45 , 12,'JPG');
      $pdf::Cell(45, 5, '', 0);
      $pdf::Cell(100, 5, '', 0,0,'C', 0);
      $pdf::SetFont('Arial', 'B', 14);
      $pdf::Cell(45, 5, '', 0,0,'R', 0);
      // $pdf::Cell(45, 5, 'V-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / S-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT), 0,0,'R', 0);
      $pdf::Ln();
      $pdf::Cell(45, 2, '', 0);
      $pdf::SetFont('Arial', 'B', 18);
      $pdf::Cell(100, 2, 'SOLICITUD DE SUSPENSIÓN', 0,0,'C', 0);
      $pdf::Cell(45, 2, '', 0);
      $pdf::Ln();
      $pdf::Cell(45, 2, '', 0);
      $pdf::Cell(100, 4, '', 0,0,'C',0);
      $pdf::Image('img/logo_jaqi.png' , 170 ,151, 30 , 6,'PNG');
      $pdf::SetFont('Arial', 'B', 12);
      $pdf::Cell(45, 4, 'S-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / V-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

      //DATOS PERSONALES
      $pdf::Ln(8);
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Nombre:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$personal[0]->fechaingreso,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
      $pdf::Ln();
      $pdf::SetFont('Arial','B',8);
      $pdf::cell(50,4,'CAS:','LB',0,'');
      $pdf::SetFont('Arial','',8);
      $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
      $pdf::Ln(7);

      //DIAS DE VACACION
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','',11);
      $pdf::cell(32,6,'Disponibles','LRB',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(32,6,($disponibles[0]->saldo),'LRB',0,'C');
      $pdf::SetFont('Arial','',11);
      $pdf::cell(32,6,'Suspendidas','BR',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(32,6,$total[0]->total,'B',0,'C');
      $pdf::SetFont('Arial','',11);
      $pdf::cell(31,6,'Saldo','LRB',0,'C');
      $pdf::SetFont('Arial','B',11);
      $pdf::cell(31,6,($disponibles[0]->saldo + $total[0]->total),'LRB',0,'C');
      $pdf::Ln(9);

      //DETALLE DE DIAS SOLICITADOS
      $pdf::SetFont('Arial','B',11);
      $pdf::SetFillColor(216, 216, 216);
      $pdf::cell(0,6,'DETALLE DE DÍAS DE SUSPENSIÓN SOLICITADOS',1, 0, 'C', true);
      $pdf::SetFillColor(255, 255, 255);
      $pdf::Ln();
      $pdf::SetFont('Arial','B',10);
      $h = round(190/count($usadas));
      foreach ($usadas as $i => $item) {
          if($i == count($usadas)-1){
              $pdf::cell(0,6,$item->title,1,"","L");
          }
          else{
              $pdf::cell($h,6,$item->title,1,"","L");
          }
      }
      $pdf::Ln();
      $pdf::SetFont('Arial','',9);

      $x = $pdf::GetX();
      $y = $pdf::GetY();
      $push_right = 0;
      $x = $pdf::GetX();
      $y = $pdf::GetY();
      $push_right = 0;
      if (count($usadas) > 0){
      $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
      $push_right += $w;
      $pdf::SetXY($x + $push_right, $y);
      }
      if (count($usadas) > 1){
      $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
      // $pdf::MultiCell($w = 63,$y-$y1,f_formato_array($item->inicio),1,'L',0);
      $push_right += $w;
      $pdf::SetXY($x + $push_right, $y);
      // $pdf::MultiCell(0,$y-$y1,"",1,'L',0);
      }
      if (count($usadas) > 2){
      $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
      }
      $pdf::Ln(9);

      $pdf::SetFont('Arial','B',10);
      $pdf::cell(24,10,'Observación:','LTB',"","L");
      $pdf::SetFont('Arial','',10);*/
      /*$pdf::cell(0,10,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
      $pdf::SetXY($pdf::GetX(), 261);
      $pdf::Ln();
      $pdf::SetFont('Arial','',10);
      //$pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
      //$pdf::cell(63,5,'','',0,'C');
      //$pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');
      $pdf::Ln();*/
      $pdf::Output();
      exit;
  }























































    public function reporte_fechas_vacaciones(){
        $usuarios=User::paginate(100);
        $hoy = new DateTime(date('Y-m-d'));
        $personal = \DB::table('personal')
        ->join('users', 'personal.cedula', '=', 'users.ci')
        ->join('cargos', 'personal.item', '=', 'cargos.num_item')
        ->join('areas', 'personal.idarea', '=', 'areas.idarea')
        ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
        ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
        ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
        ->where('gestiones.vigencia', '>', $hoy)
        ->select('personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
        'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
        'unidades.nombre as unidad', 'unidades.id as idunidad',
         'direcciones.nombre as direccion', 'gestiones.*',
        //  \DB::raw("group_concat(start SEPARATOR ', ') as fechas")
        //  \DB::raw('SUM(gestiones.computo) as tomado'),
         \DB::raw('SUM(gestiones.saldo) as saldo_total')
         )
        ->groupBy('gestiones.id_usuario')
        ->get();
        return view("pdfs.reporte_fechas_vacaciones")
        ->with("usuarios",$usuarios)
        ->with("personal", $personal);
    }

    public function reporte_rechazados(){
        $id = Auth::User()->id;
        $usuario=User::find($id);

        $personal = \DB::table('personal')
            ->join('users', 'personal.cedula', '=', 'users.ci')
            ->join('cargos', 'personal.item', '=', 'cargos.num_item')
            ->join('areas', 'personal.idarea', '=', 'areas.idarea')
            ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
            ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
            ->join('suspensiones', 'users.id', '=', 'suspensiones.id_usuario')
            ->join('vacaciones', 'suspensiones.id_vacacion', '=', 'vacaciones.id')
            ->join('estados', 'suspensiones.estado', '=', 'estados.id')
            ->join('usadas', 'suspensiones.id_vacacion', '=', 'usadas.id_solicitud')
            // ->where('personal.idarea', $persona->idarea)
            ->whereBetween('suspensiones.estado', [2, 5])
            ->whereBetween('usadas.id_estado',[9, 12])
            ->select('personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
            'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
            'unidades.nombre as unidad', 'unidades.id as idunidad',
             'direcciones.nombre as direccion', 'suspensiones.*', 'suspensiones.id as id_suspension', 'vacaciones.id as id_solicitud',
             'estados.estado',
             \DB::raw("group_concat(start SEPARATOR ', ') as fechas"),
            //  \DB::raw('SUM(usadas.usadas) as dias')
             \DB::raw('COUNT(usadas.usadas) as dias')
             )
            ->groupBy('vacaciones.id')
            ->get();
        return view('pdfs.reporte_rechazados')

            ->with('usuario', $usuario)->with('personal', $personal);
    }

    public function pdf_sol_suspension($id_suspension) {

        $suspension = Suspension::find($id_suspension);
        $id_solicitud = $suspension->id_vacacion;

        $hoy = new DateTime(date('Y-m-d'));

        $solicitud = Vacacion::find($suspension->id_vacacion);//Solicitud suspension

        $user_id = $suspension->id_usuario;
        $usuario=User::find($user_id);
        $personal = \DB::table('personal')
        ->join('users', 'personal.cedula', '=', 'users.ci')
        ->join('cargos', 'personal.id_cargo', '=', 'cargos.num_item')
        ->join('areas', 'personal.idarea', '=', 'areas.idarea')
        ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
        ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
        ->where('cedula', $usuario->ci)
        ->select('personal.*', 'users.*', 'users.id as id_usuario', 'cargos.*', 'areas.*',
        'unidades.nombre as unidad',
        'direcciones.nombre as direccion')->get();

        $total = Usada::where('id_usuario', $user_id)
        ->where('id_solicitud', $id_solicitud)
        ->whereBetween('id_estado', [9, 11])
        ->select(\DB::raw('SUM(usadas.usadas) as total'))
        ->orderBy('title', 'asc')->get();

        $cas = \DB::table('gestiones')
        ->where('gestiones.vigencia', '>', $hoy)
        ->where('id_usuario', $user_id)
        ->select('year', 'month', 'day')
        ->orderBy('id', 'desc')
        ->first();

        $disponibles = \DB::table('users')
        ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
        ->where('users.id', $user_id)
        ->where('gestiones.vigencia', '>', $hoy)
        ->select(\DB::raw('SUM(gestiones.saldo) as saldo'))
        // ->orderBy('gestiones.id', 'asc')
        ->get();

        $usadas = \DB::table('usadas')
        ->select('usadas.*', \DB::raw("group_concat(start SEPARATOR ', ') as inicio, count(start) as numero"))
        ->where('id_solicitud', $id_solicitud)
        ->whereBetween('id_estado', [9, 11])
        ->groupBy('title')
        // ->orderBy('usadas.tiempo')
        ->orderBy('numero', 'desc')
        ->get();

        $hoy = new DateTime(date('Y-m-d'));
        $alta = new DateTime($personal[0]->fechaingreso);

        if($personal[0]->fechabaja == null){//con baja
          $antiguedad = $alta->diff($hoy);
        }
        else {

          $baja = new DateTime($personal[0]->fechabaja);
          $antiguedad = $alta->diff($baja);
        }

        $a = $antiguedad->y. 'a';
        $m = $antiguedad->m. 'm';
        $d = $antiguedad->d. 'd';

        //Functions
        $n = 3;
        if(count($usadas) > 0 ){
            if(count($usadas) == 2){
                $n = 4;
            }
            if(count($usadas) == 3){
                $n = 3;
            }
        }

        $pdf = new Fpdf();
        $pdf::AddPage();

        //CABECERA
        $pdf::Image('img/logochacana.jpg' , 10 ,10, 45 , 12,'JPG');
        $pdf::Cell(45, 5, '', 0);
        $pdf::Cell(100, 5, '', 0,0,'C', 0);
        $pdf::SetFont('Arial', 'B', 14);
        $pdf::Cell(45, 5, '', 0,0,'R', 0);
        // $pdf::Cell(45, 5, 'V-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / S-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT), 0,0,'R', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::SetFont('Arial', 'B', 18);
        $pdf::Cell(100, 2, 'SOLICITUD DE SUSPENSIÓN', 0,0,'C', 0);
        $pdf::Cell(45, 2, '', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::Cell(100, 4, '', 0,0,'C',0);
        $pdf::Image('img/logo_jaqi.png' , 170 ,10, 30 , 6,'PNG');
        $pdf::SetFont('Arial', 'B', 12);
        $pdf::Cell(45, 4, 'S-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / V-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

        //DATOS PERSONALES
        $pdf::Ln(8);
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Nombre:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->fechaingreso,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'CAS:','LB',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
        $pdf::Ln(7);

        //DIAS DE VACACION
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Disponibles','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,($disponibles[0]->saldo),'LRB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Suspendidas','BR',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$total[0]->total,'B',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(31,6,'Saldo','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(31,6,($disponibles[0]->saldo + $total[0]->total),'LRB',0,'C');
        $pdf::Ln(9);

        //DETALLE DE DIAS SOLICITADOS
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DETALLE DE DÍAS DE SUSPENSIÓN SOLICITADOS',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $h = round(190/count($usadas));
        foreach ($usadas as $i => $item) {
            if($i == count($usadas)-1){
                $pdf::cell(0,6,$item->title,1,"","L");
            }
            else{
                $pdf::cell($h,6,$item->title,1,"","L");
            }
        }
        $pdf::Ln();
        $pdf::SetFont('Arial','',9);

            $x = $pdf::GetX();
            $y = $pdf::GetY();
            $push_right = 0;
            $x = $pdf::GetX();
            $y = $pdf::GetY();
            $push_right = 0;
            if (count($usadas) > 0){
            $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
            $push_right += $w;
            $pdf::SetXY($x + $push_right, $y);
            }
            if (count($usadas) > 1){
            $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
            // $pdf::MultiCell($w = 63,$y-$y1,f_formato_array($item->inicio),1,'L',0);
            $push_right += $w;
            $pdf::SetXY($x + $push_right, $y);
            // $pdf::MultiCell(0,$y-$y1,"",1,'L',0);
            }
            if (count($usadas) > 2){
            $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
            }
        $pdf::Ln(9);

        $pdf::SetFont('Arial','B',10);
        $pdf::cell(24,10,'Observación:','LTB',"","L");
        $pdf::SetFont('Arial','',10);
        $pdf::cell(0,10,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
        $pdf::Ln();
        $pdf::SetXY($pdf::GetX(), 127);
        $pdf::Ln();
        $pdf::SetFont('Arial','',10);
        $pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
        $pdf::cell(63,5,'','',0,'C');
        $pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');
        $pdf::Ln();
        $pdf::cell(0,5,'- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ',0,'','C');


        //CABECERA
        $pdf::Ln(9);
        $pdf::Image('img/logochacana.jpg' , 10 ,151, 45 , 12,'JPG');
        $pdf::Cell(45, 5, '', 0);
        $pdf::Cell(100, 5, '', 0,0,'C', 0);
        $pdf::SetFont('Arial', 'B', 14);
        $pdf::Cell(45, 5, '', 0,0,'R', 0);
        // $pdf::Cell(45, 5, 'V-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / S-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT), 0,0,'R', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::SetFont('Arial', 'B', 18);
        $pdf::Cell(100, 2, 'SOLICITUD DE SUSPENSIÓN', 0,0,'C', 0);
        $pdf::Cell(45, 2, '', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::Cell(100, 4, '', 0,0,'C',0);
        $pdf::Image('img/logo_jaqi.png' , 170 ,151, 30 , 6,'PNG');
        $pdf::SetFont('Arial', 'B', 12);
        $pdf::Cell(45, 4, 'S-'.str_pad($suspension->id, 4, "0", STR_PAD_LEFT).' / V-'.str_pad($solicitud->id_form_sol, 4, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

        //DATOS PERSONALES
        $pdf::Ln(8);
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Nombre:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->fechaingreso,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'CAS:','LB',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
        $pdf::Ln(7);

        //DIAS DE VACACION
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Disponibles','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,($disponibles[0]->saldo),'LRB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Suspendidas','BR',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$total[0]->total,'B',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(31,6,'Saldo','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(31,6,($disponibles[0]->saldo + $total[0]->total),'LRB',0,'C');
        $pdf::Ln(9);

        //DETALLE DE DIAS SOLICITADOS
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DETALLE DE DÍAS DE SUSPENSIÓN SOLICITADOS',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $h = round(190/count($usadas));
        foreach ($usadas as $i => $item) {
            if($i == count($usadas)-1){
                $pdf::cell(0,6,$item->title,1,"","L");
            }
            else{
                $pdf::cell($h,6,$item->title,1,"","L");
            }
        }
        $pdf::Ln();
        $pdf::SetFont('Arial','',9);

        $x = $pdf::GetX();
        $y = $pdf::GetY();
        $push_right = 0;
        $x = $pdf::GetX();
        $y = $pdf::GetY();
        $push_right = 0;
        if (count($usadas) > 0){
        $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
        $push_right += $w;
        $pdf::SetXY($x + $push_right, $y);
        }
        if (count($usadas) > 1){
        $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
        // $pdf::MultiCell($w = 63,$y-$y1,f_formato_array($item->inicio),1,'L',0);
        $push_right += $w;
        $pdf::SetXY($x + $push_right, $y);
        // $pdf::MultiCell(0,$y-$y1,"",1,'L',0);
        }
        if (count($usadas) > 2){
        $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
        }
        $pdf::Ln(9);

        $pdf::SetFont('Arial','B',10);
        $pdf::cell(24,10,'Observación:','LTB',"","L");
        $pdf::SetFont('Arial','',10);
        $pdf::cell(0,10,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
        $pdf::SetXY($pdf::GetX(), 261);
        $pdf::Ln();
        $pdf::SetFont('Arial','',10);
        $pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
        $pdf::cell(63,5,'','',0,'C');
        $pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');
        $pdf::Ln();
        $pdf::Output();
        exit;
    }

    public function pdf_sol_vacacion($id_solicitud) {
        $hoy = new DateTime(date('Y-m-d'));
        $solicitud = Vacacion::find($id_solicitud);
        $users = User::all();
        $user_id = $solicitud->id_usuario;
        $usuario=User::find($user_id);
        $personal = \DB::table('personal')
        ->join('users', 'personal.cedula', '=', 'users.ci')
        ->join('cargos', 'personal.id_cargo', '=', 'cargos.num_item')
        ->join('areas', 'personal.idarea', '=', 'areas.idarea')
        ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
        ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
        ->where('cedula', $usuario->ci)
        ->select('personal.*', 'users.*', 'users.id as id_usuario', 'cargos.*', 'areas.*',
        'unidades.nombre as unidad',
        'direcciones.nombre as direccion')->get();

        $total = Usada::where('id_usuario', $user_id)
        ->where('id_solicitud', $id_solicitud)
        ->select(\DB::raw('SUM(usadas.usadas) as total'))
        ->orderBy('title', 'asc')->get();

        $cas = \DB::table('gestiones')
        ->where('gestiones.vigencia', '>', $hoy)
        ->where('id_usuario', $user_id)
        ->select('year', 'month', 'day')
        ->orderBy('id', 'desc')
        ->first();

        $disponibles = \DB::table('users')
        ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
        ->where('users.id', $user_id)
        ->where('gestiones.vigencia', '>', $hoy)
        ->select(\DB::raw('SUM(gestiones.saldo) as saldo'))
        // ->orderBy('gestiones.id', 'asc')
        ->get();

        $usadas = \DB::table('usadas')
        ->select('usadas.*', \DB::raw("group_concat(start SEPARATOR ', ') as inicio, count(start) as numero"))
        ->where('id_solicitud', $id_solicitud)
        ->groupBy('title')
        // ->orderBy('usadas.tiempo')
        ->orderBy('numero', 'desc')
        ->get();

        $hoy = new DateTime(date('Y-m-d'));
        $alta = new DateTime($personal[0]->fechaingreso);

        if($personal[0]->fechabaja == null){//con baja
          $antiguedad = $alta->diff($hoy);
        }
        else {

          $baja = new DateTime($personal[0]->fechabaja);
          $antiguedad = $alta->diff($baja);
        }

        $a = $antiguedad->y. 'a';
        $m = $antiguedad->m. 'm';
        $d = $antiguedad->d. 'd';

        //Functions
        $n = 3;
        if(count($usadas) > 0 ){
            if(count($usadas) == 2){
                $n = 4;
            }
            if(count($usadas) == 3){
                $n = 3;
            }
        }


        $pdf = new Fpdf();
        $pdf::AddPage();

        //CABECERA
        $pdf::Image('img/logochacana.jpg' , 10 ,10, 45 , 12,'JPG');
        $pdf::Cell(45, 5, '', 0);
        $pdf::Cell(100, 5, '', 0,0,'C', 0);
        $pdf::SetFont('Arial', 'B', 14);
        $pdf::Cell(45, 5, '', 0,0,'R', 0);
        // $pdf::Cell(45, 5, 'V-'.str_pad($solicitud->id_form_sol, 6, "0", STR_PAD_LEFT), 0,0,'R', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::SetFont('Arial', 'B', 18);
        $pdf::Cell(100, 2, 'SOLICITUD DE VACACIÓN', 0,0,'C', 0);
        $pdf::Cell(45, 2, '', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::Cell(100, 4, '', 0,0,'C',0);
        $pdf::Image('img/logo_jaqi.png' , 170 ,10, 30 , 6,'PNG');
        $pdf::SetFont('Arial', 'B', 12);
        $pdf::Cell(45, 4, 'V-'.str_pad($solicitud->id_form_sol, 5, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

        //DATOS PERSONALES
        $pdf::Ln(8);
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Nombre:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,f_formato($personal[0]->fechaingreso),'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'CAS:','LB',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
        $pdf::Ln(7);

        //DIAS DE VACACION
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Disponibles','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$disponibles[0]->saldo,'LRB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Solicitado','B',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$total[0]->total,'LB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(31,6,'Saldo','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(31,6,($disponibles[0]->saldo - $total[0]->total),'LRB',0,'C');
        $pdf::Ln(9);

        //DETALLE DE DIAS SOLICITADOS
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DETALLE DE DÍAS SOLICITADOS',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $h = round(190/count($usadas));
        foreach ($usadas as $i => $item) {
            if($i == count($usadas)-1){
                $pdf::cell(0,6,$item->title,1,"","L");
            }
            else{
                $pdf::cell($h,6,$item->title,1,"","L");
            }
        }
        $pdf::Ln();
        $pdf::SetFont('Arial','',9);

            $x = $pdf::GetX();
            $y = $pdf::GetY();
            $push_right = 0;
            $x = $pdf::GetX();
            $y = $pdf::GetY();
            $push_right = 0;
            if (count($usadas) > 0){
            $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
            $push_right += $w;
            $pdf::SetXY($x + $push_right, $y);
            }
            if (count($usadas) > 1){
            $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
            $push_right += $w;
            $pdf::SetXY($x + $push_right, $y);
            }
            if (count($usadas) > 2){
            $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
            }

        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $pdf::cell(0,7,'Yo '.$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno.' declaro que mi trabajo se encuentra en orden.','0',"","R");
        $pdf::Ln(10);
        $pdf::SetFont('Arial','B',10);
        $pdf::cell(24,9,'Observación:','LTB',"","L");
        $pdf::SetFont('Arial','',10);
        // $pdf::cell(0,9,'','TBR',"","L");
        $pdf::cell(0,9,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
        $pdf::Ln();
        $pdf::SetXY($pdf::GetX(), 127);
        $pdf::Ln();
        $pdf::SetFont('Arial','',10);
        $pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
        $pdf::cell(63,5,'','',0,'C');
        $pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');
        $pdf::Ln();
        $pdf::cell(0,5,'- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ',0,'','C');


        //CABECERA
        $pdf::Ln(9);
        $pdf::Image('img/logochacana.jpg' , 10 ,151, 45 , 12,'JPG');
        $pdf::Cell(45, 5, '', 0);
        $pdf::Cell(100, 5, '', 0,0,'C', 0);
        $pdf::SetFont('Arial', 'B', 14);
        $pdf::Cell(45, 5, '', 0,0,'R', 0);
        // $pdf::Cell(45, 5, 'V-'.str_pad($solicitud->id_form_sol, 6, "0", STR_PAD_LEFT), 0,0,'R', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::SetFont('Arial', 'B', 18);
        $pdf::Cell(100, 2, 'SOLICITUD DE VACACIÓN', 0,0,'C', 0);
        $pdf::Cell(45, 2, '', 0);
        $pdf::Ln();
        $pdf::Cell(45, 2, '', 0);
        $pdf::Cell(100, 4, '', 0,0,'C',0);
        $pdf::Image('img/logo_jaqi.png' , 170 ,151, 30 , 6,'PNG');
        $pdf::SetFont('Arial', 'B', 12);
        $pdf::Cell(45, 4, 'V-'.str_pad($solicitud->id_form_sol, 5, "0", STR_PAD_LEFT).' / '.$solicitud->gestion, 0,0,'R', 0);

        //DATOS PERSONALES
        $pdf::Ln(8);
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DATOS PERSONALES',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Unidad Organizacional:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->unidad,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Nombre:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Fecha de ingreso:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,f_formato($personal[0]->fechaingreso),'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'Antiguedad MDCyT:','L',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$a.' '.$m.' '.$d,'R',0,'L');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',8);
        $pdf::cell(50,4,'CAS:','LB',0,'');
        $pdf::SetFont('Arial','',8);
        $pdf::cell(0,4,$cas->year.'a '.$cas->month.'m '.$cas->day.'d','RB',0,'L');
        $pdf::Ln(7);

        //DIAS DE VACACION
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DÍAS DE VACACIÓN',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Disponibles','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$disponibles[0]->saldo,'LRB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(32,6,'Solicitado','B',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(32,6,$total[0]->total,'LB',0,'C');
        $pdf::SetFont('Arial','',11);
        $pdf::cell(31,6,'Saldo','LRB',0,'C');
        $pdf::SetFont('Arial','B',11);
        $pdf::cell(31,6,($disponibles[0]->saldo - $total[0]->total),'LRB',0,'C');
        $pdf::Ln(9);

        //DETALLE DE DIAS SOLICITADOS
        $pdf::SetFont('Arial','B',11);
        $pdf::SetFillColor(216, 216, 216);
        $pdf::cell(0,6,'DETALLE DE DÍAS SOLICITADOS',1, 0, 'C', true);
        $pdf::SetFillColor(255, 255, 255);
        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $h = round(190/count($usadas));
        foreach ($usadas as $i => $item) {
            if($i == count($usadas)-1){
                $pdf::cell(0,6,$item->title,1,"","L");
            }
            else{
                $pdf::cell($h,6,$item->title,1,"","L");
            }
        }
        $pdf::Ln();
        $pdf::SetFont('Arial','',9);

        $x = $pdf::GetX();
        $y = $pdf::GetY();
        $push_right = 0;
        $x = $pdf::GetX();
        $y = $pdf::GetY();
        $push_right = 0;
        if (count($usadas) > 0){
        $pdf::MultiCell($w = $h,5,f_formato_array($usadas[0]->inicio),1,'L',0);
        $push_right += $w;
        $pdf::SetXY($x + $push_right, $y);
        }
        if (count($usadas) > 1){
        $pdf::MultiCell($w = $h,5,f_formato_array($usadas[1]->inicio).salto_n($usadas[0]->inicio, $usadas[1]->inicio, $n),1,'L',0);
        // $pdf::MultiCell($w = 63,$y-$y1,f_formato_array($item->inicio),1,'L',0);
        $push_right += $w;
        $pdf::SetXY($x + $push_right, $y);
        // $pdf::MultiCell(0,$y-$y1,"",1,'L',0);
        }
        if (count($usadas) > 2){
        $pdf::MultiCell(0,5,f_formato_array($usadas[2]->inicio).salto_n($usadas[0]->inicio, $usadas[2]->inicio, $n),1,'L',0);
        }
        $pdf::Ln();
        $pdf::SetFont('Arial','B',10);
        $pdf::cell(0,7,'Yo '.$personal[0]->nombre.' '.$personal[0]->paterno.' '.$personal[0]->materno.' declaro que mi trabajo se encuentra en orden.','0',"","R");
        $pdf::Ln(10);
        $pdf::SetFont('Arial','B',10);
        $pdf::cell(24,9,'Observación:','LTB',"","L");
        $pdf::SetFont('Arial','',10);
        // $pdf::cell(0,9,'','TBR',"","L");
        $pdf::cell(0,9,'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _','TBR',"","L");
        $pdf::Ln();
        $pdf::SetXY($pdf::GetX(), 260);
        $pdf::Ln();
        $pdf::SetFont('Arial','',10);
        $pdf::cell(63,5,'Servidor(a) Público(a)','T',0,'C');
        $pdf::cell(63,5,'','',0,'C');
        $pdf::cell(64,5,'Inmediato Superior / Superior Jerárquico','T',0,'C');

        $pdf::Output();
        exit;
    }

    public function reporte_funcionario(){//$id_min, $id_dir, $id_uni
        $id_min = $_GET['id_min'];
        $id_dir = $_GET['id_dir'];
        $id_uni = $_GET['id_uni'];
        $ranking = \DB::table('personal')
            ->join('users', 'personal.cedula', '=', 'users.ci')
            ->join('cargos', 'personal.item', '=', 'cargos.num_item')
            ->join('areas', 'personal.idarea', '=', 'areas.idarea')
            ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
            ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
            ->join('ministerios', 'areas.idmin', '=', 'ministerios.id')
            ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
            // ->join('vacaciones', 'users.id', '=', 'vacaciones.id_usuario')
            // ->join('estados', 'vacaciones.id_estado', '=', 'estados.id')
            ->join('usadas', 'gestiones.id', '=', 'usadas.id_gestion')
            //poner solo de gestiones activas
            // ->where('areas.idmin', $id_min)
            // ->where('areas.iddireccion', $id_dir)
            // ->where('areas.idunidad', $id_uni)
            // ->where('personal.idarea', $persona->idarea)
            // ->where('vacaciones.id_estado', '=', 3)
            ->select('ministerios.nombre as ministerio', 'direcciones.nombre as direccion', 'unidades.nombre as unidad', 'personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
            'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
            'unidades.nombre as unidad', 'unidades.id as idunidad',
             'direcciones.nombre as direccion',
            //  'vacaciones.*', 'vacaciones.id as id_solicitud',
            //  'estados.estado',
             \DB::raw("group_concat(start SEPARATOR ', ') as fechas"),
             \DB::raw('SUM(usadas.usadas) as dias')
             )
            ->groupBy('gestiones.id_usuario')
            ->get();
        return $ranking;
    }

    public function ranking_vacaciones(){//$id_min, $id_dir, $id_uni

        $hoy = new DateTime(date('Y-m-d'));
        // $hoy = new DateTime("2020-02-17");
        $id_min = $_GET['id_min'];
        $id_dir = $_GET['id_dir'];
        $id_uni = $_GET['id_uni'];
        $ranking = \DB::table('personal')
            ->join('users', 'personal.cedula', '=', 'users.ci')
            ->join('cargos', 'personal.item', '=', 'cargos.num_item')
            ->join('areas', 'personal.idarea', '=', 'areas.idarea')
            ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
            ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
            ->join('ministerios', 'areas.idmin', '=', 'ministerios.id')
            ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
            // ->join('vacaciones', 'users.id', '=', 'vacaciones.id_usuario')
            // ->join('estados', 'vacaciones.id_estado', '=', 'estados.id')
            // ->join('usadas', 'gestiones.id', '=', 'usadas.id_gestion')
            //poner solo de gestiones activas
            ->where('gestiones.vigencia', '>', $hoy)
            ->where('areas.idmin', $id_min)
            ->where('areas.iddireccion', $id_dir)
            ->where('areas.idunidad', $id_uni)
            // ->where('personal.idarea', $persona->idarea)
            // ->where('vacaciones.id_estado', '=', 3)
            ->select('ministerios.nombre as ministerio', 'direcciones.nombre as direccion', 'unidades.nombre as unidad',
            'personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
            'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
            'unidades.nombre as unidad', 'unidades.id as idunidad',
            'direcciones.nombre as direccion',
            'gestiones.desde', 'gestiones.hasta', 'gestiones.vigencia',
            // 'vacaciones.*', 'vacaciones.id as id_solicitud',
            // 'estados.estado',
            //  \DB::raw("group_concat(start SEPARATOR ', ') as fechas"),
             \DB::raw('SUM(gestiones.computo) as dias'),
             \DB::raw('SUM(gestiones.saldo) as total_saldo')
             )
            // ->groupBy('gestiones.id')
            ->groupBy('users.id')
            ->orderBy('total_saldo', 'desc')
            ->get();
        return $ranking;
    }

    public function reporte_ranking_vacaciones(){

        $hoy = new DateTime(date('Y-m-d'));

        $id = Auth::User()->id;
        $usuario=User::find($id);

        $ministerios = Ministerio::all();
        $direcciones = Direccion::all();
        $unidades = Unidad::all();

        $ranking = \DB::table('personal')
        ->join('users', 'personal.cedula', '=', 'users.ci')
        ->join('cargos', 'personal.item', '=', 'cargos.num_item')
        ->join('areas', 'personal.idarea', '=', 'areas.idarea')
        ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
        ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
        ->join('ministerios', 'areas.idmin', '=', 'ministerios.id')
        ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
        // ->join('vacaciones', 'users.id', '=', 'vacaciones.id_usuario')
        // ->join('estados', 'vacaciones.id_estado', '=', 'estados.id')
        // ->join('usadas', 'gestiones.id', '=', 'usadas.id_gestion')
        //poner solo de gestiones activas
        ->where('gestiones.vigencia', '>', $hoy)

        // ->where('personal.idarea', $persona->idarea)
        // ->where('vacaciones.id_estado', '=', 3)
        ->select('ministerios.nombre as ministerio', 'direcciones.nombre as direccion', 'unidades.nombre as unidad',
        'personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
        'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
        'unidades.nombre as unidad', 'unidades.id as idunidad',
        'direcciones.nombre as direccion',
        'gestiones.desde', 'gestiones.hasta', 'gestiones.vigencia',
        // 'vacaciones.*', 'vacaciones.id as id_solicitud',
        // 'estados.estado',
        //  \DB::raw("group_concat(start SEPARATOR ', ') as fechas"),
         \DB::raw('SUM(gestiones.computo) as dias'),
         \DB::raw('SUM(gestiones.saldo) as total_saldo')
         )
        // ->groupBy('gestiones.id')
        ->groupBy('users.id')
        ->orderBy('total_saldo', 'desc')
        ->get();

        $personal = \DB::table('personal')
            ->join('users', 'personal.cedula', '=', 'users.ci')
            ->join('cargos', 'personal.item', '=', 'cargos.num_item')
            ->join('areas', 'personal.idarea', '=', 'areas.idarea')
            ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
            ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
            ->join('vacaciones', 'users.id', '=', 'vacaciones.id_usuario')
            ->join('estados', 'vacaciones.id_estado', '=', 'estados.id')
            ->join('usadas', 'vacaciones.id', '=', 'usadas.id_solicitud')
            // ->where('personal.idarea', $persona->idarea)
            ->where('vacaciones.id_estado', '=', 3)
            ->select('personal.fechaingreso', 'personal.item', 'personal.idarea', 'users.id as id_usuario',
            'users.ci', 'users.nombre', 'users.paterno','users.materno', 'cargos.*', 'areas.*',
            'unidades.nombre as unidad', 'unidades.id as idunidad',
             'direcciones.nombre as direccion', 'vacaciones.*', 'vacaciones.id as id_solicitud',
             'estados.estado',
             \DB::raw("group_concat(start SEPARATOR ', ') as fechas"),
             \DB::raw('SUM(usadas.usadas) as dias')
             )
            ->groupBy('vacaciones.id')
            ->get();
        return view('pdfs.reporte_ranking_vacaciones')
            ->with('ministerios', $ministerios)
            ->with('direcciones', $direcciones)
            ->with('unidades', $unidades)
            ->with('usuario', $usuario)
            ->with('personal', $personal)
            ->with('ranking', $ranking);
    }

    public function pdf_solicitud_vacacion($id_solicitud){
        $hoy = new DateTime(date('Y-m-d'));
        $users = User::all();
        $user_id = Auth::user()->id;
        $usuario=User::find($user_id);
        $personal = \DB::table('personal')
        ->join('users', 'personal.cedula', '=', 'users.ci')
        ->join('cargos', 'personal.id_cargo', '=', 'cargos.num_item')
        ->join('areas', 'personal.idarea', '=', 'areas.idarea')
        ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
        ->join('direcciones', 'areas.iddireccion', '=', 'direcciones.id')
        ->where('cedula', $usuario->ci)
        ->select('personal.*', 'users.*', 'users.id as id_usuario', 'cargos.*', 'areas.*',
        'unidades.nombre as unidad',
        'direcciones.nombre as direccion')->get();

        $total = Usada::where('id_usuario', $user_id)
        ->where('id_solicitud', $id_solicitud)
        ->select(\DB::raw('SUM(usadas.usadas) as total'))

        ->orderBy('title', 'asc')->get();
        $disponibles = \DB::table('users')
        ->join('gestiones', 'users.id', '=', 'gestiones.id_usuario')
        ->where('users.id', $user_id)
        ->where('gestiones.vigencia', '>', $hoy)
        ->select(\DB::raw('SUM(gestiones.saldo) as saldo'))->get();

        $usadas = \DB::table('usadas')
                                ->select('usadas.*', DB::raw("group_concat(start SEPARATOR ', ') as inicio"))
                                ->where('id_solicitud', $id_solicitud)
                                ->groupBy('start')
                                ->get();

        // $solicitud = Vacacion::find($id_solicitud);
        //$solicitud->id_estado = 2; //SOLICITADA

        // if($solicitud->save()){
            return view('pdfs.pdf_solicitud_vacacion')
            ->with('total', $total)
            ->with('disponibles', $disponibles)
            ->with('usadas', $usadas)
            ->with('users', $users)
            ->with('personal', $personal);
        // }
        // else{
            // return 'Hubo un error al enviar su solicitud, verifique su conectividad';
        // }


    }
}
