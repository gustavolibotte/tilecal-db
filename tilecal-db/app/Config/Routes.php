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
$routes->set404Override('\App\Controllers\Equipments::notFound');
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
$routes->get('/', 'Home::index', ['filter' => 'auth:view']);
$routes->get('callback', 'Callback::callback', ['filter' => 'auth:view']);

// Equipment routes
$routes->get('api/equipments/get', 'Equipments::get', [
    'filter' => 'auth:view',
    'as' => 'equipment_get'
]);
$routes->get('equipments', 'Equipments::index', [
    'filter' => 'auth:view',
    'as' => 'equipment_index'
]);
$routes->get('equipments?onlyDeleted=true', 'Equipments::index', [
    'filter' => 'auth:edit',
    'as' => 'equipment_index_deleted'
]);
$routes->get('equipments?typeName=(:segment)', 'Equipments::index', [
    'filter' => 'auth:view',
    'as' => 'equipment_index_type'
]);
$routes->get('equipments/view/(:alphanum)', 'Equipments::view/$1', [
    'filter' => 'auth:view',
    'as' => 'equipment_view'
]);
$routes->match(['get', 'post'], 'equipments/register/(:segment)', 'Equipments::register/$1', [
    'filter' => 'auth:register',
    'as' => 'equipment_register_type'
]);
$routes->match(['get', 'post'], 'equipments/batch-register', 'Equipments::batchRegister', [
    'filter' => 'auth:register',
    'as' => 'equipment_batch_register'
]);
$routes->post('equipments/edit/(:alphanum)',  'Equipments::edit/$1', [
    'filter' => 'auth:edit',
    "as" => 'equipment_edit'
]);
$routes->post('equipments/delete/(:alphanum)', 'Equipments::delete/$1', [
    'filter' => 'auth:delete',
    'as' => 'equipment_delete'
]);
$routes->post('equipments/restore/(:alphanum)',  'Equipments::restore/$1', [
    'filter' => 'auth:delete',
    "as" => 'equipment_restore'
]);


// File routes
$routes->get('api/files/get', 'Files::get', [
    'filter' => 'auth:view',
    'as' => 'file_get'
]);
$routes->get('files', 'Files::index', [
    'filter' => 'auth:view',
    'as' => 'file_index'
]);
$routes->get('files?onlyDeleted=true', 'Files::index', [
    'filter' => 'auth:delete',
    'as' => 'file_index_deleted'
]);
$routes->get('files/view/(:num)', 'Files::view/$1', [
    'filter' => 'auth:view',
    'as' => 'file_view'
]);
$routes->post('files/delete/(:num)', 'Files::delete/$1', [
    'filter' => 'auth:delete',
    'as' => 'file_delete'
]);
$routes->post('files/restore/(:num)',  'Files::restore/$1', [
    'filter' => 'auth:delete',
    'as' => 'file_restore'
]);
$routes->match(['get', 'post'], 'files/upload', 'Files::upload', [
    'filter' => 'auth:upload',
    'as' => 'file_upload'
]);
$routes->get('files/download/(:num)', 'Files::download/$1', [
    'filter' => 'auth:download',
    'as' => 'file_download'
]);
$routes->get('validation/(:alphanum)', 'Files::downloadValidationLog/$1', [
    'filter' => 'auth:register',
    'as' => 'download_validation_log'
]);

// Errors
$routes->get('unauthorized', 'Equipments::unauthorized');
$routes->get('notfound', 'Equipments::notFound');
$routes->get('deleted-entry', 'Equipments::deletedEntry');
$routes->get('forbidden', 'Equipments::forbidden');
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
