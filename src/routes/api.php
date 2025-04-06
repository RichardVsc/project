<?php

use App\Http\Controllers\Transfer\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->post('/transfer', [TransferController::class, 'apiStore']);
