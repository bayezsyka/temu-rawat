<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('panel', function (User $user) {
    return $user->hasAnyRole(User::ROLES);
});

Broadcast::channel('queue.{queueId}', function (User $user, int $queueId) {
    return $user->hasAnyRole(User::ROLES);
});
