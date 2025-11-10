<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger documentation route
Route::get('api/documentation', function () {
    $documentation = 'default';
    $urlToDocs = route('l5-swagger.'.$documentation.'.docs');
    $configUrl = config('l5-swagger.defaults.additional_config_url');
    $validatorUrl = config('l5-swagger.defaults.validator_url');
    $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', true);
    $operationsSorter = config('l5-swagger.defaults.operations_sort');

    return view('vendor.l5-swagger.index', compact(
        'documentation',
        'urlToDocs',
        'configUrl',
        'validatorUrl',
        'useAbsolutePath',
        'operationsSorter'
    ));
});
