<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Hasilkan XML sitemap publik dinamis sesuai standar sitemaps.org.
     * Hanya menyertakan URL publik aktif dan mengecualikan area auth, dashboard, dan endpoint privat.
     */
    public function index(): Response
    {
        $urls = [];

        // 1. Halaman Beranda Showroom
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->format('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // 2. Halaman Kebijakan Privasi
        if (Route::has('privacy-policy')) {
            $urls[] = [
                'loc' => route('privacy-policy'),
                'lastmod' => '2026-11-14',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        // 3. Halaman Syarat dan Ketentuan
        if (Route::has('terms-of-service')) {
            $urls[] = [
                'loc' => route('terms-of-service'),
                'lastmod' => '2026-11-14',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        // 4. Halaman Kontak
        if (Route::has('contact.index')) {
            $urls[] = [
                'loc' => route('contact.index'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 5. Halaman Detail Mobil Publik yang Aktif / Tersedia
        $cars = Car::available()->orderBy('updated_at', 'desc')->get();
        foreach ($cars as $car) {
            $carUrl = Route::has('cars.public.show')
                ? route('cars.public.show', $car->slug)
                : url('/cars/'.$car->slug);

            $urls[] = [
                'loc' => $carUrl,
                'lastmod' => $car->updated_at ? $car->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Bangun dokumen XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $item) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8').'</loc>'."\n";
            $xml .= '    <lastmod>'.$item['lastmod'].'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$item['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$item['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex', // File sitemap sendiri tidak perlu diindeks sebagai halaman web
        ]);
    }
}
