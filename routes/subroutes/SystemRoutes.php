<?php

use Illuminate\Support\Facades\Route;
use Proaction\Domain\Clients\Controller\ClientSettingsController;
use Proaction\Domain\Test\Controller\TestController;
use Proaction\System\Controller\EmailLoginController;
use Proaction\System\Controller\FileUploadController;
use Proaction\System\Controller\FlashAlertController;
use Proaction\System\Controller\HomeController;
use Proaction\System\Controller\LoginController;
use Proaction\System\Controller\LogoutController;
use Proaction\System\Controller\ProactionCacheController;
use Proaction\System\Controller\SessionController;
use Proaction\System\Controller\SubscriberComponentController;
use Proaction\System\Controller\SystemController;

/**
 * Some basic system routes
 */
Route::get('/', [HomeController::class, "index"]);


/**
 * Invokables, one duty
 */
Route::post('login', LoginController::class);
Route::get('logout', LogoutController::class);

/**
 * Open session end points. get is returned as JSON for the typescript
 * components.
 */
Route::prefix('api')->group(function () {
    Route::get('proaction', ProactionCacheController::class);
});
