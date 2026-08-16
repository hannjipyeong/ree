<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api/orders?role=customer', 'GET');
$request->setUserResolver(function() { return \App\Models\User::find(7); });
$response = $kernel->handle($request);
echo $response->getContent();
