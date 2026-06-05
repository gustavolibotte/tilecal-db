<?php

namespace Config;

$routes = Services::routes();

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('NotFound');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('\App\Controllers\WebApp::index');
$routes->setAutoRoute(false);

$routes->get('/','WebApp::index', [
    'filter' => ['auth', 'action:view'],
    'as' => 'home'
]);

$routes->group('api', static function ($routes){

    $routes->get('equipment', 'Equipments::get', ['filter' => ['api_auth', 'action:view'], 'as' => 'api_equipment_get']);
    $routes->get('equipment/inconsistencies', 'Equipments::getInconsistencies', ['filter' => ['api_auth', 'action:view'], 'as' => 'api_equipment_inconsistencies']);
    $routes->post('equipment/batch-update-locations', 'Equipments::batchUpdateLocations', ['filter' => ['api_auth', 'action:edit'], 'as' => 'api_equipment_batch_update_locations']);
    $routes->get('equipment-hist', 'Equipments::getHist', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_equipment_hist_get']);
    $routes->post('equipment/batch-validate', 'Equipments::batchValidate', ['filter' =>['api_auth', 'action:register'], 'as' => 'api_equipment_batch_validate']);
    $routes->post('equipment/batch-register', 'Equipments::batchCreate', ['filter' =>['api_auth', 'action:register'], 'as' => 'api_equipment_batch_register']);
    $routes->post('equipment', 'Equipments::post', ['filter' =>['api_auth', 'action:register'], 'as' => 'api_equipment_post']);
    $routes->put('equipment', 'Equipments::put', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_equipment_put']);
    $routes->delete('equipment', 'Equipments::delete', ['filter' =>['api_auth', 'action:delete'], 'as' => 'api_equipment_delete']);
    $routes->patch('equipment', 'Equipments::restore', ['filter' =>['api_auth', 'action:delete'], 'as' => 'api_equipment_restore']);
    $routes->post('equipment/assemble', 'Equipments::assemble', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_equipment_assemble']);
    $routes->post('equipment/disassemble', 'Equipments::disassemble', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_equipment_disassemble']);
    $routes->get('equipment/children', 'Equipments::getChildren', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_equipment_children']);
    $routes->post('equipment/relate-file', 'Equipments::relateFile', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_equipment_relate_file']);
    $routes->post('equipment/unrelate-file', 'Equipments::unrelateFile', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_equipment_unrelate_file']);

    $routes->get('equipment-type', 'EquipmentType::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_equip_type_get']);
    $routes->get('location', 'Location::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_location_get']);
    $routes->get('institute', 'Institute::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_institute_get']);
    $routes->get('status', 'Status::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_status_get']);
    $routes->get('detector-location', 'DetectorLocation::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_detector_location_get']);
    $routes->get('file', 'Files::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_file_get']);
    $routes->get('file-hist', 'Files::getHist', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_file_hist_get']);
    $routes->post('file', 'Files::post', ['filter' =>['api_auth', 'action:upload'], 'as' => 'api_file_upload']);
    $routes->post('file-put', 'Files::put', ['filter' =>['api_auth', 'action:edit'], 'as' => 'api_file_edit']);
    $routes->delete('file', 'Files::delete', ['filter' =>['api_auth', 'action:delete'], 'as' => 'api_file_delete']);
    $routes->patch('file', 'Files::restore', ['filter' =>['api_auth', 'action:delete'], 'as' => 'api_file_restore']);
    $routes->get('user', 'User::get', ['filter' =>['api_auth', 'action:view'], 'as' => 'api_user_get']);
    $routes->get('search', 'Search::index', ['filter' => ['api_auth', 'action:view'], 'as' => 'api_search_get']);

    $routes->group('v1/statistics', ['filter' => ['api_auth', 'action:view']], static function ($routes) {
        $routes->get('inventory-by-type', 'Statistics::getInventoryByType');
        $routes->get('global-status', 'Statistics::getGlobalStatusDistribution');
        $routes->get('location-distribution', 'Statistics::getLocationDistribution');
        $routes->get('institute-distribution', 'Statistics::getInstituteDistribution');
        $routes->get('orphaned-components', 'Statistics::getOrphanedComponents');
        $routes->get('location-equipments', 'Statistics::getEquipmentsByLocation');
        $routes->get('owner-distribution', 'Statistics::ownerDistribution');
    });

    // --- TILECAL 3D APIs ---
    $routes->get('calorimeter/status', 'TileCal3DController::getStatus', [
        'filter' => ['api_auth', 'action:view'],
        'as' => 'api_calorimeter_status'
    ]);
    $routes->get('calorimeter/module/(:segment)', 'TileCal3DController::getModule/$1', [
        'filter' => ['api_auth', 'action:view'],
        'as' => 'api_calorimeter_module_details'
    ]);

    $env = Services::environment();
    if (!$env->isSafe()){
        return null;
    }

    $routes->group('test', static function ($routes){
        $routes->get('headers', 'Dev::headers', ['filter' =>['api_auth', 'action:delete'], 'as' => 'api_test_headers']);
    });

    $routes->options('(:any)', 'CORS::index', ['filter' =>[], 'as' => 'api_cors_option']);

    return null;
});

$routes->get('(:any)','WebApp::index', [
    'filter' => ['auth', 'action:view'],
    'as' => 'any'
]);

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}