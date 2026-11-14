<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Service;
use App\Models\TestDrive;
use App\Observers\AuditObserver;
use App\Observers\CarObserver;
use App\Observers\ServiceObserver;
use App\Observers\TestDriveObserver;
use App\Services\Contracts\WhatsAppGateway;
use App\Services\WhatsApp\FonnteGateway;
use App\Services\WhatsApp\LogWhatsAppGateway;
use App\Services\WhatsApp\TwilioGateway;
use App\Support\Timezone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppGateway::class, function () {
            $driver = config('services.whatsapp.driver', 'log');

            return match ($driver) {
                'fonnte' => new FonnteGateway((string) config('services.whatsapp.fonnte.token', '')),
                'twilio' => new TwilioGateway(
                    (string) config('services.whatsapp.twilio.sid', ''),
                    (string) config('services.whatsapp.twilio.token', ''),
                    (string) config('services.whatsapp.twilio.from', '')
                ),
                default => new LogWhatsAppGateway,
            };
        });
    }

    public function boot(): void
    {
        Carbon::macro('toWib', function () {
            /** @var Carbon $this */
            return Timezone::toWib($this);
        });

        Carbon::macro('toWibString', function (string $format = 'd M Y, H:i', bool $withSuffix = true) {
            /** @var Carbon $this */
            return Timezone::format($this, $format, $withSuffix);
        });

        Blade::directive('wib', function ($expression) {
            return "<?php echo \\App\\Support\\Timezone::format($expression); ?>";
        });

        Car::observe(CarObserver::class);
        Car::observe(AuditObserver::class);

        Service::observe(ServiceObserver::class);
        Service::observe(AuditObserver::class);

        TestDrive::observe(TestDriveObserver::class);
        TestDrive::observe(AuditObserver::class);
    }
}
