<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/menus');

Route::resource('menus', MenuController::class)->except('show');