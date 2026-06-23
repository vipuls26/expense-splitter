<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::first();
Auth::login($user);

$group = App\Models\Group::create([
    'name' => 'Curl Test Group',
    'created_by' => $user->id
]);
App\Models\GroupMember::create([
    'group_id' => $group->id,
    'user_id' => $user->id,
    'role' => 'owner'
]);

echo "Created Group ID: " . $group->id . "\n";

$request = Illuminate\Http\Request::create('/api/groups/' . $group->id, 'DELETE');
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);
echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Response Body: " . $response->getContent() . "\n";
