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
$routes->set404Override('\App\Controllers\WebApp::index');
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
$routes->get('/','WebApp::index', [
    'filter' => ['auth', 'action:view'],
    'as' => 'home'
]);

$routes->group('api', static function ($routes){

    //Equipment API
    $routes->get('equipment', 'Equipments::get', [
        'filter' => ['api_auth', 'action:view'],
        'as' => 'api_equipment_get'
    ]);
    $routes->get('equipment-hist', 'Equipments::getHist', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_equipment_hist_get'
    ]);
    $routes->post('equipment/batch-register', 'Equipments::batchCreate', [
        'filter' =>['api_auth', 'action:register'],
        'as' => 'api_equipment_batch_register'
    ]);
    $routes->post('equipment', 'Equipments::post', [
        'filter' =>['api_auth', 'action:register'],
        'as' => 'api_equipment_post'
    ]);
    $routes->put('equipment', 'Equipments::put', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_equipment_put'
    ]);
    $routes->delete('equipment', 'Equipments::delete', [
        'filter' =>['api_auth', 'action:delete'],
        'as' => 'api_equipment_delete'
    ]);
    $routes->patch('equipment', 'Equipments::restore', [
        'filter' =>['api_auth', 'action:delete'],
        'as' => 'api_equipment_restore'
    ]);
    $routes->post('equipment/assemble', 'Equipments::assemble', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_equipment_assemble'
    ]);
    $routes->post('equipment/disassemble', 'Equipments::disassemble', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_equipment_disassemble'
    ]);
    $routes->get('equipment/children', 'Equipments::getChildren', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_equipment_children'
    ]);
    $routes->post('equipment/relate-file', 'Equipments::relateFile', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_equipment_relate_file'
    ]);
    $routes->post('equipment/unrelate-file', 'Equipments::unrelateFile', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_equipment_unrelate_file'
    ]);

    // EquipmentTypes API
    $routes->get('equipment-type', 'EquipmentType::get',[
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_equip_type_get'
    ]);

    // Location API
    $routes->get('location', 'Location::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_location_get'
    ]);

    // Institute API
    $routes->get('institute', 'Institute::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_institute_get'
    ]);

    // Status API
    $routes->get('status', 'Status::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_status_get'
    ]);

    // Detector Location API
    $routes->get('detector-location', 'DetectorLocation::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_detector_location_get'
    ]);

    // File API
    $routes->get('file', 'Files::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_file_get'
    ]);
    $routes->get('file-hist', 'Files::getHist', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_file_hist_get'
    ]);
    $routes->post('file', 'Files::post', [
        'filter' =>['api_auth', 'action:upload'],
        'as' => 'api_file_upload'
    ]);
    $routes->post('file-put', 'Files::put', [
        'filter' =>['api_auth', 'action:edit'],
        'as' => 'api_file_edit'
    ]);
    $routes->delete('file', 'Files::delete', [
        'filter' =>['api_auth', 'action:delete'],
        'as' => 'api_file_delete'
    ]);
    $routes->patch('file', 'Files::restore', [
        'filter' =>['api_auth', 'action:delete'],
        'as' => 'api_file_restore'
    ]);

    // User API
    $routes->get('user', 'User::get', [
        'filter' =>['api_auth', 'action:view'],
        'as' => 'api_user_get'
    ]);

    $routes->group('v1/statistics', ['filter' => 'api_auth'], static function ($routes) {
        $routes->get('inventory-by-type', 'Statistics::getInventoryByType');
        $routes->get('global-status', 'Statistics::getGlobalStatusDistribution');
        $routes->get('location-distribution', 'Statistics::getLocationDistribution');
        $routes->get('institute-distribution', 'Statistics::getInstituteDistribution');
        $routes->get('orphaned-components', 'Statistics::getOrphanedComponents');
    });

    $env = Services::environment();
    if (!$env->isSafe()){
        return null;
    }
    
    $routes->group('test', static function ($routes){
        $routes->get('headers', 'Dev::headers', [
            'filter' =>['api_auth', 'action:delete'],
            'as' => 'api_test_headers'
        ]);
    });

    // This first route was made to comply with the CORS protocol used
    //  by the browsers.
    // https://stackoverflow.com/questions/43871637/no-access-control-allow-origin-header-is-present-on-the-requested-resource-whe
    $routes->options('(:any)', 'CORS::index',[
        'filter' =>[],
        'as' => 'api_cors_option'
    ]);

    return null;
});

// Force React App return
$routes->get('(:any)','WebApp::index', [
    'filter' => ['auth', 'action:view'],
    'as' => 'any'
]);

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
