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

        if (! $notification) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pemberitahuan tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('notifications.index')->with('error', 'Pemberitahuan tidak ditemukan.');
        }

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        $targetUrl = $this->resolveSafeTargetUrl($request, $notification->data['url'] ?? null);
        if ($request->isMethod('GET') || $request->has('redirect')) {
            return redirect($targetUrl ?? route('notifications.index'));
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
            'recent' => $request->user()->notifications()->take(5)->get()->map(function ($n) use ($request) {
                $safeUrl = $this->resolveSafeTargetUrl($request, $n->data['url'] ?? null);

                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Pemberitahuan',
                    'message' => $n->data['message'] ?? '',
                    'url' => $safeUrl ?? route('notifications.index', [], false),
                    'read_url' => route('notifications.read', ['id' => $n->id, 'redirect' => 1], false),
                    'icon' => $n->data['icon'] ?? 'bell',
                    'color' => $n->data['color'] ?? 'slate',
                    'read' => $n->read(),
                    'created_at_human' => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Validasi dan normalisasi URL tujuan notifikasi agar aman dari open-redirect atau tautan usang.
     */
    private function resolveSafeTargetUrl(Request $request, mixed $rawUrl): ?string
    {
        if (! is_string($rawUrl)) {
            return null;
        }

        $url = trim($rawUrl);
        if ($url === '' || $url === '#' || str_starts_with($url, '//')) {
            return null;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            return null;
        }

        if (isset($parsed['scheme'])) {
            $scheme = strtolower($parsed['scheme']);
            if (! in_array($scheme, ['http', 'https'], true)) {
                return null;
            }

            $host = strtolower((string) ($parsed['host'] ?? ''));
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $allowedHosts = array_filter(array_unique([
                strtolower($request->getHost()),
                is_string($appHost) ? strtolower($appHost) : null,
                'localhost',
                '127.0.0.1',
            ]));

            if ($host === '' || ! in_array($host, $allowedHosts, true)) {
                return null;
            }

            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
            $normalized = $path.$query.$fragment;
        } else {
            $normalized = str_starts_with($url, '/') ? $url : '/'.ltrim($url, '/');
        }

        $pathOnly = (string) (parse_url($normalized, PHP_URL_PATH) ?? '');
        if (preg_match('#^/dashboard/test-drives(?:/\d+)?$#', $pathOnly) === 1) {
            $user = $request->user();

            return $user && $user->isCustomer()
                ? route('test-drives.my', [], false)
                : route('dashboard', [], false);
        }

        return $normalized;
    }
}
