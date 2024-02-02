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
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('\App\Controllers\Home::index');
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

// Equipment API
$routes->get('api/equipment', 'Equipments::get', [
    'filter' => 'auth:view',
    'as' => 'api_equipment_get'
]);
$routes->get('api/equipment-hist', 'Equipments::getHist', [
    'filter' => 'auth:view',
    'as' => 'api_equipment_hist_get'
]);
$routes->post('api/equipment/batch-register', 'Equipments::batchCreate',[
    'filter' => 'auth:register',
    'as' => 'api_equipment_batch_register'
]);
$routes->post('api/equipment', 'Equipments::post', [
    'filter' => 'auth:register',
    'as' => 'api_equipment_post'
]);
$routes->put('api/equipment', 'Equipments::put', [
    'filter' => 'auth:edit',
    'as' => 'api_equipment_put'
]);
$routes->delete('api/equipment', 'Equipments::delete', [
    'filter' => 'auth:delete',
    'as' => 'api_equipment_delete'
]);
$routes->patch('api/equipment', 'Equipments::restore', [
    'filter' => 'auth:delete',
    'as' => 'api_equipment_restore'
]);
$routes->post('api/equipment/assemble', 'Equipments::assemble', [
    'filter' => 'auth:edit',
    'as' => 'api_equipment_assemble'
]);
$routes->post('api/equipment/disassemble', 'Equipments::disassemble', [
    'filter' => 'auth:edit',
    'as' => 'api_equipment_disassemble'
]);

// EquipmentTypes API
$routes->get('api/equipment-type', 'EquipmentType::get',[
  'filter' => 'auth:view',
  'as' => 'api_equip_type_get'
]);

// Location API
$routes->get('api/location', 'Location::get', [
    'filter' => 'auth:view',
    'as' => 'api_location_get'
]);

// Institute API
$routes->get('api/institute', 'Institute::get', [
    'filter' => 'auth:view',
    'as' => 'api_institute_get'
]);

// Status API
$routes->get('api/status', 'Status::get', [
    'filter' => 'auth:view',
    'as' => 'api_status_get'
]);

// Detector Location API
$routes->get('api/detector-location', 'DetectorLocation::get', [
    'filter' => 'auth:view',
    'as' => 'api_detector_location_get'
]);

// File API
$routes->get('api/file', 'Files::get', [
    'filter' => 'auth:view',
    'as' => 'api_file_get'
]);
$routes->get('api/file-hist', 'Files::getHist', [
    'filter' => 'auth:view',
    'as' => 'api_file_hist_get'
]);
$routes->post('api/file', 'Files::post', [
    'filter' => 'auth:upload',
    'as' => 'api_file_upload'
]);
$routes->post('api/file-put', 'Files::put', [
    'filter' => 'auth:edit',
    'as' => 'api_file_edit'
]);
$routes->delete('api/file', 'Files::delete', [
  'filter' => 'auth:delete',
  'as' => 'api_file_delete'
]);
$routes->patch('api/file', 'Files::restore', [
  'filter' => 'auth:delete',
  'as' => 'api_file_restore'
]);

// User API
$routes->get('api/user', 'User::get', [
  'filter' => 'auth:view',
  'as' => 'api_user_get'
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
