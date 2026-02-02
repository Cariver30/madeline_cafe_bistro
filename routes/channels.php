<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('kitchen.area.{areaId}', function ($user, $areaId) {
    return $user->hasRole(['kitchen', 'manager']);
});

Broadcast::channel('kitchen.label.{labelId}', function ($user, $labelId) {
    return $user->hasRole(['kitchen', 'manager']);
});

Broadcast::channel('server.{serverId}', function ($user, $serverId) {
    return $user->isManager() || $user->id === (int) $serverId;
});

Broadcast::channel('manager.orders', function ($user) {
    return $user->hasRole(['manager']);
});

Broadcast::channel('host.waiting-list', function ($user) {
    return $user->hasRole(['host', 'manager']);
});
