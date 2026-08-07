<?php

use App\Models\Admin;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Admin.{id}', function ($user, int $id): bool {
    return $user instanceof Admin && (int) $user->id === $id;
});

Broadcast::channel('App.Models.VendorUser.{id}', function ($user, int $id): bool {
    return $user instanceof VendorUser && (int) $user->id === $id;
});
