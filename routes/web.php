<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', static function (Request $request) {
    return response()->json($request->user());
});
