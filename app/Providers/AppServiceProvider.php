<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        /*view()->composer('*', function($view) {
            $personas = \DB::table('personal')
            ->join('users', 'personal.cedula', '=', 'users.ci')
            ->join('areas', 'personal.idarea', '=', 'areas.idarea')
            ->join('unidades', 'areas.idunidad', '=', 'unidades.id')
            ->select('users.id as id_usuario', 'users.nombre', 'users.paterno','users.materno', 'users.ci',
                    'areas.*', 'unidades.nombre as unidad', 'unidades.id as idunidad')
            ->get();
            $view->with('personas', $personas);
        });*/

        //Establecemos la variable personas para ser accedida desde cualquier lugar del codigo
        //Seleccionamos los principales datos de todos los usuarios, para posteriormente seleccionar el del usuario logueado
        view()->composer('*', function($view) {
            $personas = \DB::table('personas')
            ->join('users', 'personas.id_persona', '=', 'users.id_persona')
            ->join('servidores_publicos', 'servidores_publicos.id_servidor_publico', '=', 'users.id_servidor_publico')
            ->join('cargos', 'cargos.id_cargo', '=', 'servidores_publicos.id_cargo')
            ->join('areas', 'areas.id_area', '=', 'cargos.id_area')
            ->select('users.id as id_usuario', 'personas.id_persona', 'personas.nombres', 'personas.paterno','personas.materno', 'personas.titulo', 'personas.nombre_foto',
                    'cargos.descripcion as descripcion_del_cargo', 'areas.sigla as sigla_del_area', 'areas.nombre as nombre_del_area')
            ->get();
            $view->with('personas', $personas);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
