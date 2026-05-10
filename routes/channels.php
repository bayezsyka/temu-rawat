<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('staff-panel', function (User $user) {
    return $user->hasAnyRole(User::ROLES);
});

Broadcast::channel('doctor.{doctorId}', function (User $user, int $doctorId) {
    return $user->isAdmin() || $user->isAsisten() || $user->doctor?->id === $doctorId;
});

Broadcast::channel('queue.{queueId}', function (User $user, int $queueId) {
    return $user->hasAnyRole(User::ROLES);
});
