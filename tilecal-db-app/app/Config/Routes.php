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
$routes->set404Override();
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
$routes->get('/', 'Home::index');

// Equipments routes
$routes->get('equipments', 'Equipments::index');
$routes->get('equipments/deleted', 'Equipments::deletedIndex');
$routes->post('equipments/delete/(:alphanum)', 'Equipments::delete/$1');
$routes->get('equipments/(:segment)', 'Equipments::index/$1');
$routes->get('equipments/view/(:alphanum)', 'Equipments::view/$1');

// Equipment register views
// $routes->get('equipments/fenics/register', 'Fenics::registerGet');
$routes->get('equipments/pmt/register', 'PMT::registerGet');
$routes->post('equipments/pmt/register', 'PMT::registerPost');
$routes->get('equipments/edit/(:segment)/(:alphanum)', 'PMT::edit/$1/$2');
$routes->post('equipments/edit/(:segment)/(:alphanum)', 'PMT::edit/$1/$2');

// File routes
$routes->get('files', 'Files::index');
$routes->get('files/deleted', 'Files::deletedIndex');
$routes->get('files/view/(:alphanum)', 'Files::view/$1');
$routes->get('files/upload', 'Files::uploadGet');
$routes->post('files/upload', 'Files::uploadPost');
$routes->get('files/(:alphanum)', 'Files::download/$1');

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
