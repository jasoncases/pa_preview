<?php

use Illuminate\Support\Str;

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


/*
|--------------------------------------------------------------------------
| Proaction Routes
|--------------------------------------------------------------------------
|
| All Proaction routes are organized in the /subroutes dir. All files
| pattern match /{Domain}Routes.php
|
*/

foreach (glob(__DIR__ . "/subroutes/*Routes.php") as $filename) {
    require_once $filename;
}
