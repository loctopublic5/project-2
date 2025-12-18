<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::post('/register', function(){
    return 'thank yyou!';
});
