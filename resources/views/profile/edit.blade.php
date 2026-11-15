<x-dashboard-layout title="Profil Akun" breadcrumb="Pengaturan Profil">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <h1 class="font-display text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Profil Akun</h1>
            <p class="text-sm text-slate-600 mt-1">Kelola informasi pribadi, alamat email terverifikasi, dan keamanan kata sandi akun Anda.</p>
        </div>

        <div class="p-6 sm:p-8 bg-white border border-slate-200/80 shadow-sm rounded-3xl">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white border border-slate-200/80 shadow-sm rounded-3xl">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white border border-slate-200/80 shadow-sm rounded-3xl">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-dashboard-layout>
