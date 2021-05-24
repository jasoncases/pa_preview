<?php

use Illuminate\Support\Facades\Route;
use Proaction\Domain\Timesheets\Controller\TimelineCascadeController;
use Proaction\Domain\Timesheets\Controller\TimelineController;
use Proaction\Domain\Timesheets\Controller\TimesheetActionsController;
use Proaction\Domain\Timesheets\Controller\TimesheetController;
use Proaction\Domain\Timesheets\Controller\TimesheetEditController;

Route::get('timeline/{id}', TimelineController::class);
Route::get('timeline_cascade/{id}/{date}', TimelineCascadeController::class);
Route::post('timesheet_actions', TimesheetActionsController::class);

Route::put('/timestamp_edit/{id}', [TimesheetEditController::class, 'update']);

Route::get('timesheet', [TimesheetController::class, "index"]);
Route::post('timesheet', [TimesheetController::class, "store"]);
Route::get('timesheet/bydate', [TimesheetController::class, "getDateByDate"]);
Route::get('timesheet/create', [TimesheetController::class, "create"]);
Route::get('timesheet/{id}/break', [TimesheetController::class, "getBreakStatus"]);
Route::get('timesheet/{timesheet}', [TimesheetController::class, "show"]);
Route::delete('timesheet/{timesheet}', [TimesheetController::class, "delete"]);
Route::put('timesheet/{timesheet}', [TimesheetController::class, "update"]);
Route::get('timesheet/{timesheet}/edit', [TimesheetController::class, "edit"]);
Route::get('timesheets', [TimesheetController::class, "index"]);
Route::post('timesheets', [TimesheetController::class, "store"]);
Route::get('timesheets/create', [TimesheetController::class, "create"]);
Route::get('timesheets/{timesheet}', [TimesheetController::class, "show"]);
Route::put('timesheets/{timesheet}', [TimesheetController::class, "update"]);
Route::delete('timesheets/{timesheet}', [TimesheetController::class, "destroy"]);
Route::get('timesheets/{timesheet}/edit', [TimesheetController::class, "edit"]);
