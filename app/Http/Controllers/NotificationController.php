<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman pusat notifikasi pengguna.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query = $user->unreadNotifications();
        }

        $notifications = $query->paginate(10)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        return view('dashboard.notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        $targetUrl = $notification?->data['url'] ?? null;
        if ($targetUrl && $request->has('redirect')) {
            return redirect($targetUrl);
        }

        return back()->with('success', 'Pemberitahuan ditandai telah dibaca.');
    }

    /**
     * Tandai semua notifikasi pengguna sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua pemberitahuan telah ditandai dibaca.');
    }

    /**
     * Ambil jumlah notifikasi belum dibaca untuk polling real-time topbar.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
            'recent' => $request->user()->notifications()->take(5)->get()->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Pemberitahuan',
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? '#',
                'icon' => $n->data['icon'] ?? 'bell',
                'color' => $n->data['color'] ?? 'slate',
                'read' => $n->read(),
                'created_at_human' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }
}
