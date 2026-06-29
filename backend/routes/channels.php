<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);


Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    // Check if user is a member of the group
    return $user->groups()->where('groups.id', $groupId)->exists();
});
