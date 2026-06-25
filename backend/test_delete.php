<?php

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$user = User::first();
Auth::login($user);

$group = Group::create([
    'name' => 'Curl Test Group',
    'created_by' => $user->id,
]);
GroupMember::create([
    'group_id' => $group->id,
    'user_id' => $user->id,
    'role' => 'owner',
]);

echo 'Created Group ID: '.$group->id."\n";

$request = Request::create('/api/groups/'.$group->id, 'DELETE');
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);
echo 'Response Status: '.$response->getStatusCode()."\n";
echo 'Response Body: '.$response->getContent()."\n";
