<?php

use Kasi\Support\Facades\Route;

Route::get('/foo', function () {
    return 'Regular route';
});

Route::get('{slug}', function () {
    return 'Wildcard route';
});
