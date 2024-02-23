<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('NotFound');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
// returns home on 404 to return the react app
$routes->set404Override('\App\Controllers\NotFound::index');
$routes->setAutoRoute(false);
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/','Home::index', [
    'filter' => 'auth:view',
    'as' => 'home'
]);
$routes->get('callback', 'Callback::callback', [
    'filter' => 'auth:view'
]);

// API routes
// This first route was made to comply with the CORS protocol used
//  by the browsers.
// https://stackoverflow.com/questions/43871637/no-access-control-allow-origin-header-is-present-on-the-requested-resource-whe
$routes->options('api(:any)', 'CORS::index',[
    'filter' => 'auth:view',
    'as' => 'api_cors_option'
]);

$routes->group('api', ['filter' => 'api_auth'], static function ($routes){

    //Equipment API
    $routes->get('equipment', 'Equipments::get', [
        'as' => 'api_equipment_get'
    ]);
    $routes->get('equipment-hist', 'Equipments::getHist', [
        'as' => 'api_equipment_hist_get'
    ]);
    $routes->post('equipment/batch-register', 'Equipments::batchCreate',[
        'as' => 'api_equipment_batch_register'
    ]);
    $routes->post('equipment', 'Equipments::post', [
        'as' => 'api_equipment_post'
    ]);
    $routes->put('equipment', 'Equipments::put', [
        'as' => 'api_equipment_put'
    ]);
    $routes->delete('quipment', 'Equipments::delete', [
        'as' => 'api_equipment_delete'
    ]);
    $routes->patch('equipment', 'Equipments::restore', [
        'as' => 'api_equipment_restore'
    ]);
    $routes->post('api/equipment/assemble', 'Equipments::assemble', [
        'as' => 'api_equipment_assemble'
    ]);
    $routes->post('api/equipment/disassemble', 'Equipments::disassemble', [
        'as' => 'api_equipment_disassemble'
    ]);

    // EquipmentTypes API
    $routes->get('equipment-type', 'EquipmentType::get',[
        'as' => 'api_equip_type_get'
    ]);

    // Location API
    $routes->get('location', 'Location::get', [
        'as' => 'api_location_get'
    ]);

    // Institute API
    $routes->get('institute', 'Institute::get', [
        'as' => 'api_institute_get'
    ]);

    // Status API
    $routes->get('status', 'Status::get', [
        'as' => 'api_status_get'
    ]);

    // Detector Location API
    $routes->get('detector-location', 'DetectorLocation::get', [
        'as' => 'api_detector_location_get'
    ]);

    // File API
    $routes->get('file', 'Files::get', [
        'as' => 'api_file_get'
    ]);
    $routes->get('file-hist', 'Files::getHist', [
        'as' => 'api_file_hist_get'
    ]);
    $routes->post('file', 'Files::post', [
        'as' => 'api_file_upload'
    ]);
    $routes->post('file-put', 'Files::put', [
        'as' => 'api_file_edit'
    ]);
    $routes->delete('file', 'Files::delete', [
        'as' => 'api_file_delete'
        ]);
    $routes->patch('file', 'Files::restore', [
        'as' => 'api_file_restore'
        ]);
    $routes->get('file/download', 'Files::download', [
        'as' => 'api_file_download'
    ]);
    $routes->get('file/download-hist', 'Files::downloadHist', [
        'as' => 'api_file_download_hist'
    ]);

    // User API
    $routes->get('user', 'User::get', [
        'as' => 'api_user_get'
    ]);
    $env = Services::environment();
    if (!$env->isSafe()){
        return null;
    }
    $routes->group('test', static function ($routes){
        $routes->get('headers', 'Dev::headers', [
            'as' => 'api_test_headers'
        ]);
    });

    return null;
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
