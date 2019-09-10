<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    Route::get('/home', 'HomeController@index')->name('home');

    //Correspondencia
    Route::get('form_nueva_correspondencia', 'CorrespondenciasController@form_nueva_correspondencia');
    Route::post('nueva_correspondencia', 'CorrespondenciasController@nueva_correspondencia');
    Route::get('bandeja', 'CorrespondenciasController@bandeja');
    Route::post('leer_correspondencia', 'CorrespondenciasController@leer_correspondencia');
    Route::get('imprimir_correspondencia/{id_derivacion}', 'CorrespondenciasController@imprimir_correspondencia');
    Route::post('guardar_copia_correspondencia', 'CorrespondenciasController@guardar_copia_correspondencia');
    Route::get('copias_recibidas', 'CorrespondenciasController@copias_recibidas');
    Route::get('form_derivar_correspondencia/{id_derivacion}', 'CorrespondenciasController@form_derivar_correspondencia');
    Route::post('derivar_correspondencia', 'CorrespondenciasController@derivar_correspondencia');
    Route::get('form_juntar_y_derivar_correspondencia/{id_derivacion}', 'CorrespondenciasController@form_derivar_correspondencia');
    Route::post('juntar_y_derivar_correspondencia', 'CorrespondenciasController@derivar_correspondencia');
    Route::get('form_archivar_correspondencia/{id_derivacion}', 'CorrespondenciasController@form_archivar_correspondencia');
    Route::post('archivar_correspondencia', 'CorrespondenciasController@archivar_correspondencia');
    Route::get('enviados', 'CorrespondenciasController@enviados');
    Route::get('archivados', 'CorrespondenciasController@archivados');
    Route::get('reimprimir_hri/{id_derivacion_codificado}', 'CorrespondenciasController@reimprimir_hri');
    Route::get('reimprimir_comunicacion_interna/{id_derivacion_codificado}/{posicion}', 'CorrespondenciasController@reimprimir_comunicacion_interna');
    Route::get('imprimir_comunicacion_interna_todo/{cite_codificado}/{ruta_vuelta_codificado}', 'CorrespondenciasController@imprimir_comunicacion_interna_todo');
    Route::get('seguimiento_buscar', 'CorrespondenciasController@seguimiento_buscar');
    Route::post('seguimiento', 'CorrespondenciasController@seguimiento');
    Route::get('manual', 'CorrespondenciasController@manual');

    //Mensajes de exito
    Route::get('msj_exitoso/{accion_exitosa_codificado}', 'CorrespondenciasController@msj_exitoso');
    Route::get('msj_exitoso_imprimir/{accion_exitosa_codificado}/{id_derivacion_codificado}', 'CorrespondenciasController@msj_exitoso_imprimir');

    //Rperotes generados en pdf
    Route::get('pdf_comunicacion_interna/{id_derivacion}', 'PdfController@pdf_comunicacion_interna')->name('pdf_comunicacion_interna');
    Route::get('pdf_reimprimir_comunicacion_interna/{id_derivacion_codificado}/{posicion}', 'PdfController@pdf_reimprimir_comunicacion_interna')->name('pdf_reimprimir_comunicacion_interna');
    Route::get('pdf_imprimir_comunicacion_interna_todo/{cite_codificado}', 'PdfController@pdf_imprimir_comunicacion_interna_todo')->name('pdf_imprimir_comunicacion_interna_todo');


    Route::get('pdf_hoja_ruta_interna/{id_derivacion}', 'PdfController@pdf_hoja_ruta_interna')->name('pdf_hoja_ruta_interna');
    //Route::get('pdf_hoja_ruta_interna/{id_derivacion}', 'PdfController@pdf_hoja_ruta_interna')->name('pdf_hoja_ruta_interna');
    //Route::get('reimprimir_hri/{cite_codificado}', 'CorrespondenciasController@reimprimir_hri');








    Route::get('form_pruebas', 'PruebasController@form_pruebas');

    Route::get('/listado_usuarios', 'UsuariosController@listado_usuarios');
    Route::post('crear_usuario', 'UsuariosController@crear_usuario');
    Route::post('editar_usuario', 'UsuariosController@editar_usuario');
    Route::post('buscar_usuario', 'UsuariosController@buscar_usuario');
    Route::post('borrar_usuario', 'UsuariosController@borrar_usuario');
    Route::post('editar_acceso', 'UsuariosController@editar_acceso');


    Route::post('crear_rol', 'UsuariosController@crear_rol');
    Route::post('crear_permiso', 'UsuariosController@crear_permiso');
    Route::post('asignar_permiso', 'UsuariosController@asignar_permiso');
    Route::get('quitar_permiso/{idrol}/{idper}', 'UsuariosController@quitar_permiso');

    Route::get('form_nuevo_usuario', 'UsuariosController@form_nuevo_usuario');
    Route::get('form_nuevo_rol', 'UsuariosController@form_nuevo_rol');
    Route::get('form_nuevo_permiso', 'UsuariosController@form_nuevo_permiso');
    Route::get('form_editar_usuario/{id}', 'UsuariosController@form_editar_usuario');
    Route::get('form_baja_usuario/{id}', 'UsuariosController@form_baja_usuario');
    Route::get('confirmacion_borrado_usuario/{idusuario}', 'UsuariosController@confirmacion_borrado_usuario');
    Route::get('asignar_rol/{idusu}/{idrol}', 'UsuariosController@asignar_rol');
    Route::get('quitar_rol/{idusu}/{idrol}', 'UsuariosController@quitar_rol');
    Route::get('form_borrado_usuario/{idusu}', 'UsuariosController@form_borrado_usuario');
    Route::get('borrar_rol/{idrol}', 'UsuariosController@borrar_rol');

    //directorio
    Route::get('form_nuevo_contacto', 'DirectorioController@form_nuevo_contacto');
    Route::get('form_editar_contacto/{id}', 'DirectorioController@form_editar_contacto');
    Route::post('crear_contacto', 'DirectorioController@crear_contacto');
    Route::post('editar_contacto', 'DirectorioController@editar_contacto');

    Route::post('buscar_persona', 'DirectorioController@buscar_persona');
    Route::get('listado_personas/{filtro?}/{orden?}', 'DirectorioController@listado_personas');
    Route::any('buscar_persona', 'DirectorioController@buscar_persona');

    Route::resource('listado_empresas', 'DirectorioController@listado_empresas');
    Route::resource('listado_empresas_data', 'DirectorioController@data_empresas');

    //Direcciones
    Route::get('/form_info_direcciones', 'VacacionController@form_info_direcciones');
    Route::get('consultaDirecciones/{id}', 'DireccionController@consultaDirecciones');

    //Unidades
    Route::get('consultaUnidades/{id}', 'UnidadesController@consultaUnidades');

    //Cargos
    Route::get('consultaCargos/{id}', 'CargosController@consultaCargos');

    //Vacaciones
    Route::get('/form_info_areas', 'VacacionController@form_info_areas');
    Route::get('/form_sol_vacacion_usuario', 'VacacionController@form_sol_vacacion_usuario');
    Route::get('/form_sol_vacacion/{id}', 'VacacionController@form_sol_vacacion');
    Route::post('crear_solicitud', 'VacacionController@crear_solicitud');
    Route::post('autorizar_solicitud', 'VacacionController@autorizar_solicitud');
    Route::post('verificar_solicitud', 'VacacionController@verificar_solicitud');

    Route::get('form_sol_vacacion_unidad/{id}', 'VacacionController@form_sol_vacacion_unidad');
    Route::get('form_sol_vacacion_rr_hh/{id}', 'VacacionController@form_sol_vacacion_rr_hh');
    Route::get('form_anulacion_vacacion/{id}', 'VacacionController@form_anulacion_vacacion');
    Route::post('anular_solicitud', 'VacacionController@anular_solicitud');
    Route::get('form_sol_emergencias_usuario/{id}', 'VacacionController@form_sol_emergencias_usuario');


    Route::get('/listado_solicitudes_unidad', 'VacacionController@listado_solicitudes_unidad');
    Route::get('/listado_solicitudes_rr_hh', 'VacacionController@listado_solicitudes_rr_hh');
    Route::get('/listado_solicitudes_usuario', 'VacacionController@listado_solicitudes_usuario');

    Route::post('editar_tiempo', 'VacacionController@editar_tiempo');//con resp ajax
    Route::post('editar_fecha', 'VacacionController@editar_fecha');//drag sin ajax
    Route::post('agregar_fechas', 'VacacionController@agregar_fechas');//con resp ajax
    Route::get('crear_sol', 'VacacionController@crear_sol');
    Route::post('borrar_sol', 'VacacionController@borrar_sol');
    Route::post('reprobar_solicitud', 'VacacionController@reprobar_solicitud');
    Route::get('agregar_solicitud', 'VacacionController@agregar_solicitud');

    Route::post('editar_solicitud', 'VacacionController@editar_solicitud');//con resp ajax
    Route::post('cancelar_suspension', 'VacacionController@cancelar_suspension');
    Route::post('guardar_suspension', 'VacacionController@guardar_suspension');

    //DUODECIMAS
    Route::get('/listado_usuarios_duo', 'UsuariosController@listado_usuarios_duo');
    Route::post('buscar_usuario_duo', 'UsuariosController@buscar_usuario_duo');

    //Usuarios
    Route::get('form_agregar_usuario', 'UsuariosController@form_agregar_usuario');
    Route::get('reporte_usuarios', 'UsuariosController@reporte_usuarios');

    //Calendario
    Route::get('form_calendar', 'CalendarioController@form_calendar');
    Route::get('calendar_datos', 'CalendarioController@calendar_datos');
    Route::get('estado_calendario/{id_sol}', 'CalendarioController@estado_calendario');
    Route::get('calendar_datos_suspension/{id}', 'CalendarioController@calendar_datos_suspension');
    Route::get('calendar_datos_emergencias/{id}', 'CalendarioController@calendar_datos_emergencias');

    //Gestion
    Route::get('form_nueva_gestion', 'GestionController@form_nueva_gestion');
    Route::post('crear_gestion', 'GestionController@crear_gestion');
    Route::get('form_editar_gestion/{id}', 'GestionController@form_editar_gestion');
    Route::post('editar_gestion', 'GestionController@editar_gestion');

    //Suspension
    Route::get('form_sol_suspension_usuario/{id_sol}', 'SuspensionController@form_sol_suspension_usuario');
    Route::get('form_sol_suspension_unidad/{id_sol}', 'SuspensionController@form_sol_suspension_unidad');
    Route::get('form_sol_suspension_rr_hh/{id_sol}', 'SuspensionController@form_sol_suspension_rr_hh');

    Route::get('/listado_suspension_usuario', 'SuspensionController@listado_suspension_usuario');
    Route::get('/listado_suspension_unidad', 'SuspensionController@listado_suspension_unidad');
    Route::get('/listado_suspension_rr_hh', 'SuspensionController@listado_suspension_rr_hh');

    Route::post('aceptar_suspension_unidad', 'SuspensionController@aceptar_suspension_unidad');
    Route::post('aceptar_suspension_rr_hh', 'SuspensionController@aceptar_suspension_rr_hh');

    //PDF
    Route::get('pdf_solicitud_vacacion/{id}', 'PdfController@pdf_solicitud_vacacion');
    Route::get('pdf_sol_vacacion/{id}', 'PdfController@pdf_sol_vacacion');
    Route::get('pdf_sol_suspension/{id}', 'PdfController@pdf_sol_suspension');
    Route::get('reporte_ranking_vacaciones', 'PdfController@reporte_ranking_vacaciones');
    Route::get('reporte_fechas_vacaciones', 'PdfController@reporte_fechas_vacaciones');
    Route::get('reporte_rechazados', 'PdfController@reporte_rechazados');
    Route::get('ranking_vacaciones', 'PdfController@ranking_vacaciones');

    Route::get('pdf_prueba', 'PdfController@pdf_prueba');

    //FERIADOS
    Route::get('form_feriado', 'FeriadoController@form_feriado');
    Route::get('form_calendario_feriado', 'FeriadoController@form_calendario_feriado');
    Route::get('calendario_feriados', 'FeriadoController@calendario_feriados');
    Route::post('agregar_feriado', 'FeriadoController@agregar_feriado');
    Route::post('editar_feriado', 'FeriadoController@editar_feriado');

});
