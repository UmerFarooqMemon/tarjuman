<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesDatabaseNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    use HandlesDatabaseNotifications;

    public function __construct()
    {
        $this->middleware('auth:admin');
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
        return auth('admin')->user();
    }
}
