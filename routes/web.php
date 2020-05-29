<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('seguridad/login', 'seguridad\LoginController@index')->name('login');
Route::post('seguridad/login', 'seguridad\LoginController@login')->name('login_post');
Route::get('seguridad/logout', 'seguridad\LoginController@logout')->name('logout');
Route::get('/','inicioController@Index')->name('inicio');
Route::group(['prefix' => 'admin', 'namespace' => 'admin', 'middleware' => ['auth', 'superadmin']], function () {
    Route::get('', 'adminController@index');
    Route::get('permission', 'permissionController@index')->name('permission');
    Route::get('permission/create', 'permissionController@create')->name('create_permission');
    /*RUTAS DEL MENU*/
    Route::get('menu', 'menuController@index')->name('menu');
    Route::get('menu/create', 'menuController@create')->name('crear_menu');
    Route::post('menu', 'menuController@store')->name('guardar_menu');
    Route::get('menu/{id}/edit', 'menuController@edit')->name('editar_menu');
    Route::put('menu/{id}', 'menuController@update')->name('actualizar_menu');
    Route::get('menu/{id}/destroy', 'menuController@destroy')->name('eliminar_menu');
    Route::post('menu/guardar-orden', 'menuController@guardarOrden')->name('guardar_orden');
    /*RUTAS ROL*/
    Route::get('rol', 'rolController@index')->name('rol');
    Route::get('rol/create', 'rolController@create')->name('crear_rol');
    Route::post('rol', 'rolController@store')->name('guardar_rol');
    Route::get('rol/{id}/edit', 'rolController@edit')->name('editar_rol');
    Route::put('rol/{id}', 'rolController@update')->name('actualizar_rol');
    Route::delete('rol/{id}', 'rolController@destroy')->name('eliminar_rol');
    /*RUTAS MENU_ROL*/
    Route::get('menu-rol', 'menurolController@index')->name('menu_rol');
    Route::post('menu-rol', 'menurolController@store')->name('guardar_menu_rol');
    
    /*RUTAS ROL_USUARIO*/
    Route::get('rol-user', 'roluserController@index')->name('rol_user');
    Route::get('rol-user/create', 'roluserController@create')->name('crear_rol_user');
    Route::post('rol-user', 'roluserController@store')->name('guardar_rol_user');
    Route::get('rol-user/{id}/edit', 'roluserController@edit')->name('editar_rol_user');
    Route::put('rol-user/{id}', 'roluserController@update')->name('actualizar_rol_user');
    Route::delete('rol-user/{id}', 'roluserController@destroy')->name('eliminar_rol_user');
    

});

Route::group(['middleware' => ['auth']], function() {
    /* RUTAS PROGRAMACION DE TURNOS */
Route::get('shiftprogramming/copyRol','shiftprogrammingController@copyRol')->name('copiarRol');
Route::post('shiftprogramming/subirArchivo', 'shiftprogrammingController@subirArchivo')->name('subir_archivo');
Route::post('shiftprogramming/guardar', 'shiftprogrammingController@guardar')->name('guardar_programacion');
Route::get('shiftprogramming/archivo/{id}', 'shiftprogrammingController@pdf')->name('pdf_nuevo');
Route::get('shiftprogramming/turnos', 'shiftprogrammingController@turnos')->name('turnos');
Route::get('shiftprogramming/newRow','shiftprogrammingController@newRow')->name('nuevo_renglon');
Route::get('shiftprogramming/workShift','shiftprogrammingController@workShift')->name('recuperar_turno');
Route::get('shiftprogramming/recoverPDF','shiftprogrammingController@recoverPDF')->name('recuperarPDF');
Route::get('shiftprogramming/copyRol','shiftprogrammingController@copyRol')->name('copiarRol');
Route::get('shiftprogramming/rotRol','shiftprogrammingController@rotRol')->name('rotarRol');
Route::get('shiftprogramming/editRol','shiftprogrammingController@editRol')->name('editarRol');
Route::get('shiftprogramming/newShift','shiftprogrammingController@newShift')->name('nueva_planeacion');
Route::get('shiftprogramming/{id}', 'shiftprogrammingController@index')->name('programacion');
});

/* RUTAS DE REPORTES */
Route::get('report/datosReporte/{type}/{datos}', 'ReporteController@datosReporteSecretaria')->name('reporte_secretaria');
Route::get('report/hrReport', 'ReporteController@hrReport')->name('reporte_secretaria');
Route::get('report/prueba','ReporteController@prueba')->name('prueba');
Route::get('report/reportES/{type}', 'ReporteController@esReport')->name('reporteES');
Route::get('report/generarReporteES','ReporteController@reporteESView')->name('generarreporteES');
Route::get('report/generarReporteRegs/{type}','ReporteController@registriesReport')->name('generarreporteRegs');
Route::get('report/reporteRegistros','ReporteController@reporteRegistrosView')->name('generarreporteRegistros');
Route::get('report/reporteRetardos','ReporteController@genDelayReport')->name('generarreporteRetardos');
Route::get('report/viewReporteRetardos','ReporteController@delaysReport')->name('reporteRetardos');
Route::get('report/percepcionesvariables','ReporteController@genHrExReport')->name('generarreportepervariables');
Route::get('report/viewpercepvariables','ReporteController@hrExtReport')->name('reportepercepvariables');

/* RUTAS DE USUARIO */
Route::get('user/change', 'userController@change')->name('cambio_usuario');
Route::put('user/{id}/cambio', 'userController@updatePassword')->name('actualizar_contraseña');
Route::get('user', 'userController@index')->name('usuario');
Route::get('user/create', 'userController@create')->name('crear_usuario');
Route::post('user', 'userController@store')->name('guardar_usuario');
Route::get('user/{id}/edit', 'userController@edit')->name('editar_usuario');
Route::put('user/{id}', 'userController@update')->name('actualizar_usuario');
Route::delete('user/{id}', 'userController@destroy')->name('eliminar_usuario');


/* RUTAS DE EMPRESAS */
Route::get('company', 'companyController@index')->name('company');
Route::post('company', 'companyController@store')->name('save_company');
Route::put('company/{id}', 'companyController@update')->name('update_company');
Route::delete('company/{id}', 'companyController@destroy')->name('delete_company');

/* RUTAS DE SINCRONIZACIÓN */
Route::get('/syncronize', 'SyncController@toSyncronize')->name('syncErp');

/* RUTAS DE EMPLEADOS */
Route::delete('employee/fingerprint/{id}', 'employeeController@desactivar')->name('desactivar');
Route::delete('employee/fingerprint/disable/{id}', 'employeeController@activar')->name('activar');
Route::get('employee/fingerprint', 'employeeController@fingerprints')->name('huellas');
Route::get('employee/fingerprint/disable', 'employeeController@fingerprintsDisable')->name('huellasActivar');
Route::get('employee/{id}/editFinger','employeeController@fingerprintEdit')->name('editarhuella');
Route::put('employee/fingerprint/{id}', 'employeeController@Editfingerprint')->name('edicionhuella');
Route::get('employee/supervisorsView', 'employeeController@supervisorsView')->name('supervisores');
Route::get('employee/{id}/editShortname', 'employeeController@editShortname')->name('editar_nombrecorto');
Route::put('employee/supervisorsView/{id}', 'employeeController@updateShortname')->name('actualizar_nombrecorto');
Route::get('employee', 'employeeController@index')->name('empleado');
Route::get('employee/create', 'employeeController@create')->name('crear_empleado');
Route::post('employee', 'employeeController@store')->name('guardar_empleado');
Route::get('employee/{id}/edit', 'employeeController@edit')->name('editar_empleado');
Route::put('employee/{id}', 'employeeController@update')->name('actualizar_empleado');
Route::delete('employee/{id}', 'employeeController@destroy')->name('eliminar_empleado');

/* RUTAS DE GRUPOS TURNOS */
Route::get('group', 'groupController@index')->name('grupo');
Route::get('group/{id}/mostrar', 'groupController@mostrar')->name('ver_grupo');
Route::get('group/create', 'groupController@create')->name('crear_grupo');
Route::post('group', 'groupController@store')->name('guardar_grupo');
Route::get('group/{id}/edit', 'groupController@edit')->name('editar_grupo');
Route::put('group/{id}', 'groupController@update')->name('actualizar_grupo');
Route::delete('group/{id}', 'groupController@destroy')->name('eliminar_grupo');

/* RUTAS DE PLANTILLA HORARIOS */
Route::get('schedule', 'scheduleController@index')->name('plantilla');
Route::get('schedule/create', 'scheduleController@create')->name('crear_plantilla');
Route::post('schedule', 'scheduleController@store')->name('guardar_plantilla');
Route::get('schedule/{id}/edit', 'scheduleController@edit')->name('editar_plantilla');
Route::put('schedule/{id}', 'scheduleController@update')->name('actualizar_plantilla');
Route::delete('schedule/{id}', 'scheduleController@destroy')->name('eliminar_plantilla');

/* RUTAS DE ASIGNACION HORARIOS */
Route::get('assign/programming/schedule_template','assignController@schedule_template')->name('agregar');
Route::get('assign/viewProgramming/{id}','assignController@viewProgramming')->name('index_programacion');
Route::get('assign/showProgramming/{id}/{dgroup}','assignController@editProgramming')->name('editar_programacion');
Route::get('assign/specificDate/{id}','assignController@viewSpecificDate')->name('fecha_especifica');
Route::post('assign/mostrarFecha', 'assignController@mostrarFecha')->name('mostrar_fecha');
Route::get('assign', 'assignController@index')->name('asignacion');
Route::get('assign/create/{id}', 'assignController@create')->name('crear_asignacion');
Route::get('assign/programming/{id}', 'assignController@programming')->name('programar');
Route::post('assign', 'assignController@store')->name('guardar_asignacion');
Route::post('assign/guardar', 'assignController@guardar')->name('guardar');
Route::get('assign/{id}/edit', 'assignController@edit')->name('editar_asignacion');
Route::put('assign/actualizar/{id}', 'assignController@actualizar')->name('actulizacion');
Route::put('assign/{id}', 'assignController@update')->name('actualizar_asignacion');
Route::delete('assign/{id}', 'assignController@destroy')->name('eliminar_asignacion');
Route::delete('assign/programming/{id}', 'assignController@eliminar')->name('eliminar');

// 
Route::get('assignone', 'assignController@indexOneDay')->name('asignar_uno');
Route::post('assignone', 'assignController@storeOne')->name('guardar_uno');
Route::put('assignone/{id}', 'assignController@updateOne')->name('actualizar_uno');
Route::delete('assignone/{id}', 'assignController@deleteOne')->name('eliminar_uno');
Route::get('assignonedata', 'assignController@getData')->name('get_data');

/* RUTAS DE ASIGNACION HOLIDAYS */
Route::get('holidayassign', 'holidayassignController@index')->name('asignacion_festivo');
Route::get('holidayassign/create/{id}', 'holidayassignController@create')->name('crear_asignacion_festivo');
Route::post('holidayassign', 'holidayassignController@store')->name('guardar_asignacion_festivo');
Route::get('holidayassign/{id}/edit', 'holidayassignController@edit')->name('editar_asignacion_festivo');
Route::put('holidayassign/{id}', 'holidayassignController@update')->name('actualizar_asignacion_festivo');
Route::delete('holidayassign/{id}', 'holidayassignController@destroy')->name('eliminar_asignacion_festivo');
//
/* RUTAS AREAS */
Route::get('area', 'areaController@index')->name('area');
Route::get('area/create', 'areaController@create')->name('crear_area');
Route::post('area', 'areaController@store')->name('guardar_area');
Route::get('area/{id}/edit', 'areaController@edit')->name('editar_area');
Route::put('area/{id}', 'areaController@update')->name('actualizar_area');
Route::delete('area/{id}', 'areaController@destroy')->name('eliminar_area');

/* RUTAS DEPARTAMENTOS */
Route::get('department', 'departmentController@index')->name('departamento');
Route::get('department/create', 'departmentController@create')->name('crear_departamento');
Route::post('department', 'departmentController@store')->name('guardar_departamento');
Route::get('department/{id}/edit', 'departmentController@edit')->name('editar_departamento');
Route::put('department/{id}', 'departmentController@update')->name('actualizar_departamento');
Route::delete('department/{id}', 'departmentController@destroy')->name('eliminar_departamento');
Route::put('upddepartments', 'departmentController@updateDepts')->name('actualizar_departamentos');

/* RUTAS DEPARTAMENTOS */
Route::get('deptsgroup', 'DeptsGroupController@index')->name('depts_grp');
Route::post('deptsgroup', 'DeptsGroupController@store')->name('guardar_grupodepts');
Route::delete('deptsgroup/{id}', 'DeptsGroupController@delete')->name('eliminar_grupodepts');
Route::put('deptsgroup/{id}/{name}', 'DeptsGroupController@edit')->name('actualizar_grupodepts');

/* RUTAS PUESTOS */
Route::get('job', 'jobController@index')->name('puesto');
Route::get('job/create', 'jobController@create')->name('crear_puesto');
Route::post('job', 'jobController@store')->name('guardar_puesto');
Route::get('job/{id}/edit', 'jobController@edit')->name('editar_puesto');
Route::put('job/{id}', 'jobController@update')->name('actualizar_puesto');
Route::delete('job/{id}', 'jobController@destroy')->name('eliminar_puesto');

/* RUTAS CAPTURA INCIDENTES */
Route::get('incidents', 'incidentController@index')->name('incidentes');
Route::get('incidents/create', 'incidentController@create')->name('crear_incidente');
Route::post('incidents', 'incidentController@store')->name('guardar_incidente');
Route::get('incidents/{id}/edit', 'incidentController@edit')->name('editar_incidente');
Route::put('incidents/{id}', 'incidentController@update')->name('actualizar_incidente');
Route::delete('incidents/{id}', 'incidentController@destroy')->name('eliminar_incidente');


/* RUTAS DIAS FESTIVOS */
Route::get('holidays', 'holidayController@index')->name('festivo');
Route::get('holidays/create', 'holidayController@create')->name('crear_festivo');
Route::post('holidays', 'holidayController@store')->name('guardar_festivo');
Route::get('holidays/{id}/edit', 'holidayController@edit')->name('editar_festivo');
Route::put('holidays/{id}', 'holidayController@update')->name('actualizar_festivo');
Route::delete('holidays/{id}', 'holidayController@destroy')->name('eliminar_festivo');

/* RUTAS DIAS FESTIVOS AUXILIARES (Guardias sabatinas)*/
Route::post('holidaysaux', 'holidayController@storeAux')->name('festivo_aux');
Route::delete('holidaysaux/{id}', 'holidayController@destroyAux')->name('eliminar_festivo_aux');
Route::put('holidaysaux/{id}', 'holidayController@updateAux')->name('actualizar_festivo_aux');

/* RUTAS TIPOS INCIDENTES */
Route::get('type_incidents', 'typeincidentController@index')->name('tipo_incidentes');
Route::get('type_incidents/create', 'typeincidentController@create')->name('crear_tipoincidente');
Route::post('type_incidents', 'typeincidentController@store')->name('guardar_tipoincidente');
Route::get('type_incidents/{id}/edit', 'typeincidentController@edit')->name('editar_tipoincidente');
Route::put('type_incidents/{id}', 'typeincidentController@update')->name('actualizar_tipoincidente');
Route::delete('type_incidents/{id}', 'typeincidentController@destroy')->name('eliminar_tipoincidente');

/* RUTAS PERMISO */
Route::get('permission', 'permissionController@index')->name('permiso');
Route::get('permission/create', 'permissionController@create')->name('crear_permiso');
Route::post('permission', 'permissionController@store')->name('guardar_permiso');
Route::get('permission/{id}/edit', 'permissionController@edit')->name('editar_permiso');
Route::put('permission/{id}', 'permissionController@update')->name('actualizar_permiso');
Route::delete('permission/{id}', 'permissionController@destroy')->name('eliminar_permiso');

/* RUTAS TURNO */
Route::get('workshift', 'workshiftController@index')->name('turno');
Route::get('workshift/create', 'workshiftController@create')->name('crear_turno');
Route::post('workshift', 'workshiftController@store')->name('guardar_turno');
Route::get('workshift/{id}/edit', 'workshiftController@edit')->name('editar_turno');
Route::put('workshift/{id}', 'workshiftController@update')->name('actualizar_turno');
Route::delete('workshift/{id}', 'workshiftController@destroy')->name('eliminar_turno');





/* RUTAS PERMISO_ROL */
Route::get('permiso-rol', 'permisorolController@index')->name('permiso_rol');
Route::get('permiso-rol', 'permisorolController@store')->name('guardar_permiso_rol');



Route::get('/home', 'HomeController@index')->name('home');
