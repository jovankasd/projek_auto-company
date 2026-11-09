# UI/UX & Design Guidelines Specification

**Concept:** Hyper-Minimalist Automotive Showcase & Modern Kinetic 2.5D Experience
**Version:** 2.0.0
**Target:** Desktop 1920×1080 (Primary), Tablet 1024×768, Mobile 375px+
**Accessibility:** WCAG 2.1 Level AA
**Motion Philosophy:** Kinetic but Purposeful
```

---

## 1. Design Token

### 1.1. Color System

**Canvas & Surface**

| Token | Hex | Tailwind |
|:---|:---|:---|
| Canvas Base | `#F9FAFB` | `bg-gray-50` |
| Surface | `#FFFFFF` | `bg-white` |
| Surface Muted | `#F1F5F9` | `bg-slate-100` |
| Overlay Frosted | `rgba(255,255,255,0.80)` | `bg-white/80` |

**Typography**

| Token | Hex | Tailwind |
|:---|:---|:---|
| Text Primary | `#0F172A` | `text-slate-900` |
| Text Secondary | `#334155` | `text-slate-700` |
| Text Tertiary | `#64748B` | `text-slate-500` |
| Text Disabled | `#CBD5E1` | `text-slate-300` |
| Backdrop Text | `#E2E8F0` | `text-slate-200` + `mix-blend-multiply` |

**Brand**

| Token | Hex | Tailwind |
|:---|:---|:---|
| Onyx | `#000000` | `bg-black` |
| Cobalt | `#2563EB` | `bg-blue-600` |
| Cobalt Hover | `#1D4ED8` | `bg-blue-700` |
| Cobalt Soft | `#EFF6FF` | `bg-blue-50` |

**Status Semantic**

| Status | Bg | Text | Border | Tailwind Pair |
|:---|:---|:---|:---|:---|
| Pending | `#FEF3C7` | `#B45309` | `#FDE68A` | `bg-amber-100 text-amber-700 border-amber-200` |
| Confirmed | `#E0E7FF` | `#4338CA` | `#C7D2FE` | `bg-indigo-100 text-indigo-700 border-indigo-200` |
| In Progress | `#DBEAFE` | `#1D4ED8` | `#BFDBFE` | `bg-blue-100 text-blue-700 border-blue-200` |
| Completed | `#DCFCE7` | `#15803D` | `#BBF7D0` | `bg-green-100 text-green-700 border-green-200` |
| Rejected/Cancelled | `#FEE2E2` | `#B91C1C` | `#FECACA` | `bg-red-100 text-red-700 border-red-200` |

> Semua kombinasi ≥ 4.5:1. Jangan turunkan opacity teks status di bawah `text-*-700`.

### 1.2. Typography

| Token | Font | Weight | Size | LH | Tracking |
|:---|:---|:---|:---|:---|:---|
| Display Backdrop | Sans | 900 | `clamp(4rem,16vw,18rem)` | 0.85 | `tracking-tighter` |
| Hero Title | Sans | 800 | `clamp(2.5rem,6vw,5rem)` | 1.05 | `tracking-tight` |
| Section Header | Sans | 700 | `text-3xl`→`text-5xl` | 1.15 | `tracking-tight` |
| Body | Sans | 400 | `text-base` | 1.6 | normal |
| Caption | Sans | 500 | `text-xs` | 1.4 | `tracking-wide` + uppercase |
| Metrics | Mono | 600 | `text-3xl`→`text-5xl` | 1.0 | `tracking-tight` |
| Ticket Number | Mono | 600 | `text-sm` | 1.0 | `tracking-widest` |

Font stack: `Inter` (sans), `JetBrains Mono` (mono).

Display Backdrop: `select-none pointer-events-none aria-hidden mix-blend-multiply z-0`.

### 1.3. Spacing Scale
`1=4px · 2=8 · 3=12 · 4=16 · 6=24 · 8=32 · 12=48 · 16=64 · 24=96 · 32=128`

### 1.4. Radius
`sm=6px (rounded-md) · md=8 (rounded-lg) · lg=12 (rounded-xl) · xl=16 (rounded-2xl) · full=9999`

### 1.5. Shadow

| Token | CSS | Tailwind |
|:---|:---|:---|
| xs | `0 1px 2px rgba(15,23,42,.05)` | `shadow-sm` |
| sm | `0 1px 3px rgba(15,23,42,.08)` | `shadow` |
| md | `0 4px 12px rgba(15,23,42,.08)` | `shadow-md` |
| lg | `0 10px 24px rgba(15,23,42,.10)` | `shadow-lg` |
| xl | `0 20px 40px rgba(15,23,42,.12)` | `shadow-xl` |
| 2xl | `0 25px 50px rgba(15,23,42,.18)` | `shadow-2xl` |
| car | `drop-shadow(0 25px 25px rgba(0,0,0,.25))` | custom |

### 1.6. Motion

| Token | Durasi | Easing |
|:---|:---|:---|
| instant | 100ms | linear |
| fast | 200ms | `cubic-bezier(.4,0,.2,1)` |
| base | 300ms | `cubic-bezier(.4,0,.2,1)` |
| slow | 500ms | `power2.out` |
| hero | 850ms | `back.out(1.2)` |
| slide | 1000ms | `power3.out` |

### 1.7. Breakpoints
`375 (default) · 480 (sm) · 768 (md) · 1024 (lg) · 1280 (xl) · 1536 (2xl)`

### 1.8. Z-Index Hierarchy
```
z-0   Background Ambient & Giant Typography
z-10  Car PNG + Drop Shadow
z-20  Metric Cards / Spec HUD / CTA
z-30  Sticky Navbar
z-40  Dropdown / Toast
z-50  Modal
z-60  Modal di atas modal
z-70  Loading overlay global
```

> Tidak boleh `z-[999]`. Gunakan token.

---

## 2. Layering Architecture (2.5D)

```
┌─────────────────────────────────────────────┐
│ [z-30] NAVBAR                              │
├─────────────────────────────────────────────┤
│   M U S T A N G   G T     ← [z-0] backdrop │
│          🚗               ← [z-10] car      │
│   ┌────┐┌────┐┌────┐┌────┐ ← [z-20] HUD    │
│   │450 ││529 ││4.3s││8AT │                 │
│   └────┘└────┘└────┘└────┘                 │
│   [ Test Drive ] [ Booking Servis ]         │
│              ‹ 01/05 ›                      │
└─────────────────────────────────────────────┘
```

**Aturan:**
- Parent hero: `relative h-[100dvh] overflow-hidden`.
- Mobil: `absolute inset-0 m-auto` center X+Y.
- Backdrop text lebih besar dari mobil, overflow hidden.
- HUD tidak menutupi bagian penting mobil.
- Navbar `fixed top-0 w-full backdrop-blur-md bg-white/70 border-b border-slate-200/60`.

---

## 3. Motion & Animation Protocol

### 3.1. Prinsip
Purposeful · Fast in slow out · Choreographed · Interruptible.

### 3.2. Swiper Config
```js
{
  effect: 'fade', fadeEffect: { crossFade: true },
  speed: 1000, allowTouchMove: true, grabCursor: true,
  keyboard: { enabled: true }, loop: true, preloadImages: false,
  lazy: { loadPrevNext: true },
  a11y: { enabled: true, prevSlideMessage: 'Mobil sebelumnya', nextSlideMessage: 'Mobil berikutnya' },
  on: { slideChangeTransitionStart(sw) { runHeroTimeline(sw); } }
}
```

### 3.3. Hero Timeline
```js
function runHeroTimeline(swiper) {
  const slide  = swiper.slides[swiper.activeIndex];
  const car    = slide.querySelector('[data-car-img]');
  const bgText = slide.querySelector('[data-bg-text]');
  const specs  = slide.querySelectorAll('[data-spec-item]');
  const meta   = slide.querySelector('[data-car-meta]');
  const tl = gsap.timeline({ defaults: { overwrite: 'auto' } });

  tl.fromTo(car,
    { x: 150, opacity: 0, scale: 0.90, filter: 'drop-shadow(0 15px 15px rgba(0,0,0,.15))' },
    { x: 0, opacity: 1, scale: 1, filter: 'drop-shadow(0 25px 25px rgba(0,0,0,.25))',
      duration: 0.85, ease: 'back.out(1.2)', delay: 0.15 })
   .fromTo(bgText, { y: 40, opacity: 0 }, { y: 0, opacity: 1, duration: 0.70, ease: 'power3.out' }, '-=0.70')
   .fromTo(meta,   { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.50, ease: 'power2.out' }, '-=0.50')
   .fromTo(specs,  { y: 20, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.08, duration: 0.50, ease: 'power2.out' }, '-=0.35');

  return tl;
}
```

**Exit:** `gsap.to(prevCar, { x: -120, opacity: 0, scale: 0.95, duration: 0.5, ease: 'power2.in' })`

### 3.4. Reduced Motion
```js
if (matchMedia('(prefers-reduced-motion: reduce)').matches) {
  gsap.defaults({ duration: 0.01, ease: 'none' });
}
```
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: .01ms !important;
    transition-duration: .01ms !important;
  }
}
```

### 3.5. Performance Guard
`will-change: transform, opacity` hanya saat animasi · Hindari animasi `box-shadow`/`filter` massal · Pakai `translate3d()` · `tl.kill()` saat slide berubah cepat · Debounce resize 150ms.

### 3.6. Micro-interaction

| Elemen | Trigger | Animasi |
|:---|:---|:---|
| Primary Button | Hover | `scale-105 duration-200` |
| Primary Button | Active | `scale-95 duration-100` |
| Primary Button | Focus | `ring-2 ring-black ring-offset-2` |
| Card | Hover | `-translate-y-1 shadow-lg duration-300` |
| Input | Focus | `ring-2 ring-black border-black` |
| Modal | Open | Backdrop fade 200ms + panel scale .96→1 fade 300ms |
| Toast | Enter | Slide dari kanan + fade 300ms |
| Skeleton | Loop | `animate-pulse` |

---

## 4. Component Library

### 4.1. Button

| Variant | Kelas |
|:---|:---|
| Primary | `bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-800 hover:scale-105 active:scale-95 focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:hover:scale-100` |
| Secondary | `bg-white text-slate-900 border border-slate-300 px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-black transition duration-200` |
| Accent | `bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 hover:scale-105 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 transition duration-200` |
| Ghost | `text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-100 transition` |
| Danger | `bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition` |
| Icon | `p-2 rounded-full hover:bg-slate-100 transition` |

Size: `sm=px-3 py-1.5 text-sm · md=px-6 py-3 text-base · lg=px-8 py-4 text-lg`.

### 4.2. Input

```html
<div class="space-y-2">
  <label for="field" class="block text-sm font-medium text-slate-700">
    Label <span class="text-red-600">*</span>
  </label>
  <input id="field" name="field" type="text"
    class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-white
           text-slate-900 placeholder:text-slate-400
           focus:outline-none focus:ring-2 focus:ring-black focus:border-black
           disabled:bg-slate-50 disabled:text-slate-400
           aria-invalid:border-red-500 aria-invalid:ring-red-500 transition duration-200" />
  <p id="field-error" role="alert" class="text-sm text-red-600">Pesan error</p>
</div>
```

### 4.3. Status Badge

```html
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
             text-xs font-semibold uppercase tracking-wide
             bg-amber-100 text-amber-700 border border-amber-200">
  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
  Menunggu
</span>
```

### 4.4. Card

**Standard:** `bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md hover:-translate-y-1 transition duration-300`

**Metric:** judul `text-xs uppercase text-slate-500` + angka `font-mono text-4xl font-semibold` + delta `text-sm text-green-600`.

### 4.5. Modal (Frosted Glass)

```html
<div x-show="open" x-cloak role="dialog" aria-modal="true"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="open = false">
  <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
  <div class="relative w-full max-w-lg rounded-2xl
              backdrop-blur-md bg-white/80 border border-white/20 shadow-2xl
              p-6 sm:p-8 max-h-[90vh] overflow-y-auto
              transform transition duration-300 ease-out"
       x-transition:enter="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
    <!-- content -->
  </div>
</div>
```

Focus trap · `Esc` menutup · `body` overflow-hidden · fokus kembali ke trigger.

### 4.6. Toast

```html
<div class="fixed top-24 right-6 z-40 space-y-3" role="status" aria-live="polite">
  <!-- toast item dengan border-green-200/red-200/blue-200, bg-white/90 backdrop-blur-md -->
</div>
```

### 4.7. Skeleton
`<div class="animate-pulse space-y-4"><div class="h-8 bg-slate-200 rounded-lg w-1/3"></div></div>`

### 4.8. Empty State
Ikon `w-16 h-16 rounded-full bg-slate-100` + heading + deskripsi + CTA.

### 4.9. Table
Container `bg-white rounded-2xl border border-slate-200 overflow-hidden` · Header `bg-slate-50 text-xs uppercase text-slate-500` · Row `border-t border-slate-100 hover:bg-slate-50` · Mobile → card stack (`md:table`).

### 4.10. Sidebar
`fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 -translate-x-full lg:translate-x-0 transition duration-300`

---

## 5. Layout Rules

### 5.1. Hero 2.5D
| Aturan | Nilai |
|:---|:---|
| Tinggi | `h-[100dvh]` |
| Overflow | `overflow-hidden` |
| Mobil | `absolute inset-0 m-auto` |
| Backdrop | `absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2` |
| HUD desktop | `grid grid-cols-4 gap-8 bottom-24` |
| HUD mobile | `flex gap-3 overflow-x-auto snap-x bottom-28` |
| CTA | `bottom-12` center |
| Nav slider | `bottom-6` center |

### 5.2. Dashboard
```
┌─────────┬──────────────────────────────┐
│ Sidebar │ Topbar (breadcrumb + user)   │
│  w-64   ├──────────────────────────────┤
│         │ Page Header                  │
│  Nav    │ ┌────┐┌────┐┌────┐┌────┐      │
│         │ │KPI ││KPI ││KPI ││KPI │      │
│         │ └────┘└────┘└────┘└────┘      │
│         │ Content / Table / Timeline     │
└─────────┴──────────────────────────────┘
```
Container: `max-w-[1440px] mx-auto px-6 lg:px-8 py-8` · KPI: `grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6`.

---

## 6. Responsive Fallback

| Breakpoint | Showroom | HUD | Dashboard |
|:---|:---|:---|:---|
| < 768px | Backdrop `clamp(3rem,22vw,6rem)`, mobil `max-w-[90vw]` | Horizontal scroll snap-x | Drawer, KPI 1 kolom, tabel→card |
| 768–1023 | `clamp(5rem,18vw,10rem)` | Grid 2×2 | Drawer, KPI 2 kolom |
| 1024–1279 | Full | Grid 4 kolom | Sidebar fixed, KPI 4 kolom |
| ≥ 1280 | Full, 16vw | Grid 4 kolom | Container max 1440px |

---

## 7. Accessibility (WCAG 2.1 AA)

| Kriteria | Implementasi |
|:---|:---|
| 1.1.1 Non-text | `alt` semua img; dekoratif `alt=""` + `aria-hidden` |
| 1.3.1 Info & Rel | Heading hierarkis; `<label>` semua input |
| 1.4.3 Contrast | ≥ 4.5:1 |
| 2.1.1 Keyboard | Semua fungsi accessible |
| 2.1.2 No Trap | Modal tutup dengan Esc |
| 2.4.1 Bypass | Skip-to-content |
| 2.4.7 Focus Visible | `focus-visible:ring-2 ring-black ring-offset-2` |
| 2.5.5 Target Size | Min 44×44px |
| 3.3.1 Error ID | Pesan spesifik + `aria-describedby` |
| 4.1.2 Name/Role/Value | ARIA lengkap |
| 4.1.3 Status | Toast `role="status"` + `aria-live="polite"` |

```html
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-70 focus:px-4 focus:py-2 focus:bg-black focus:text-white focus:rounded-lg">
  Lewati ke konten utama
</a>
```

---

## 8. Microcopy

**Tone:** Profesional-hangat · Ringkas · Aktif · Bahasa Indonesia.

| Konteks | Copy |
|:---|:---|
| Empty servis | "Belum ada jadwal servis. Yuk, booking sekarang." |
| Loading | "Memuat..." |
| Error generik | "Terjadi kesalahan. Silakan coba lagi." |
| Error validasi | "Tanggal servis tidak boleh sebelum hari ini." |
| Sukses booking servis | "Booking servis berhasil! Nomor tiket: SRV-20250101-0001" |
| Sukses test drive | "Pengajuan test drive terkirim. Tim sales akan menghubungi Anda." |
| Konfirmasi cancel | "Yakin ingin membatalkan? Tindakan ini tidak dapat dibatalkan." |
| Guest redirect | "Silakan masuk untuk melanjutkan. Data Anda sudah kami simpan." |

**Format Data:** Harga `Rp450.000.000` · Tanggal `15 Januari 2025` · Waktu `14:30 WIB` · Ticket `SRV-20250115-0042` · Power `450 HP` · Torque `529 Nm` · Accel `4.3s`.

---

## 9. Asset Pipeline

| Aset | Format | Max | Catatan |
|:---|:---|:---|:---|
| Mobil transparan | PNG-24/WebP | 800KB | Transparan |
| Gallery | WebP | 250KB | 3 ukuran 400/800/1600 |
| Avatar | WebP | 100KB | 256×256 |
| Banner | WebP | 300KB | 1200×400 |
| Ikon | SVG | 5KB | Sprite |
| Font | WOFF2 | 100KB | Subset Latin, swap |

Non-hero: `loading="lazy"` + `decoding="async"` · Hero slide 1: `loading="eager" fetchpriority="high"` · Wajib `width`+`height` eksplisit.

---

## 10. Do & Don't

| ✅ DO | ❌ DON'T |
|:---|:---|
| Pakai token warna | Arbitrary `bg-[#3b82f6]` |
| Z-index hierarchy | `z-[9999]` |
| Easing token | `ease-in-out` tanpa alasan |
| `<label>` eksplisit | Placeholder sebagai label |
| Feedback untuk setiap aksi | Aksi tanpa feedback |
| Hormati `prefers-reduced-motion` | Paksa animasi ke semua |
| Target ≥ 44px | Tombol kecil di mobile |
| Empty state informatif | Halaman kosong tanpa penjelasan |
| Skeleton loader | Spinner kosong |
| Kontras ≥ 4.5:1 | Teks abu-abu terang |
