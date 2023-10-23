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
$routes->set404Override('\App\Controllers\Errors::notFound');
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

// API Routes
$routes->get('api/equipments/get', 'Equipments::getEquipments', ['filter' => 'auth:view']);

// Equipments routes
$routes->get('equipments', 'Equipments::index', ['filter' => 'auth:view']);
$routes->get('equipments/deleted', 'Equipments::deletedIndex', ['filter' => 'auth:delete']);
$routes->post('equipments/delete/(:alphanum)', 'Equipments::delete/$1', ['filter' => 'auth:delete']);
$routes->post('equipments/restore/(:alphanum)',  'Equipments::restore/$1', ['filter' => 'auth:delete']);
$routes->get('equipments/view/(:alphanum)', 'Equipments::view/$1', ['filter' => 'auth:view']);
$routes->get('equipments/(:segment)', 'Equipments::index/$1', ['filter' => 'auth:view']);

// Equipment register views
$routes->get('equipments/(:segment)/register', 'Equipments::register/$1', ['filter' => 'auth:register']);
$routes->post('equipments/(:segment)/register', 'Equipments::register/$1', ['filter' => 'auth:register']);
$routes->post('equipments/(:segment)/edit/(:alphanum)','Equipments::edit/$2/$1', ['filter' => 'auth:edit']);

// File routes
$routes->get('files', 'Files::index', ['filter' => 'auth:view']);
$routes->get('files/deleted', 'Files::deletedIndex', ['filter' => 'auth:delete']);
$routes->post('files/delete/(:num)', 'Files::delete/$1', ['filter' => 'auth:delete']);
$routes->post('files/restore/(:num)',  'Files::restore/$1', ['filter' => 'auth:delete']);
$routes->get('files/view/(:alphanum)', 'Files::view/$1', ['filter' => 'auth:view']);
$routes->get('files/upload', 'Files::upload', ['filter' => 'auth:upload']);
$routes->post('files/upload', 'Files::upload', ['filter' => 'auth:upload']);
$routes->get('files/(:alphanum)', 'Files::download/$1', ['filter' => 'auth:download']);

// Errors
$routes->get('unauthorized', 'Errors::unauthorized');
$routes->get('notfound', 'Errors::notFound');
$routes->get('deleted-entry', 'Errors::deletedEntry');
$routes->get('forbidden', 'Errors::forbidden');
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
