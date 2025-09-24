<?php

use App\Models\Tenant;
use App\Models\CentralUser;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $centralUser = CentralUser::create([
        'name' => 'Ali',
        'email' => 'ali@example.com',
        'password' => bcrypt('123456'),
    ]);

    $tenant = Tenant::find('foo');

    $centralUser->tenants()->attach($tenant->id);
});
