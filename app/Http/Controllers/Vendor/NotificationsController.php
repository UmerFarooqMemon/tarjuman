<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Concerns\HandlesDatabaseNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    use HandlesDatabaseNotifications;

    public function __construct()
    {
        $this->middleware('auth:vendor');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->notificationsIndex($request);
    }

    public function markRead(string $id): JsonResponse
    {
        return $this->notificationsMarkRead($id);
    }

    public function markAllRead(): JsonResponse
    {
        return $this->notificationsMarkAllRead();
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->notificationsDestroy($id);
    }

    protected function notificationUser(): mixed
    {
        return auth('vendor')->user();
    }
}
