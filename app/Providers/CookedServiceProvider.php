<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CookedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // @tnd($millimes) => "28,000 TND"
        Blade::directive('tnd', fn ($expr) => "<?php echo e(\\App\\Support\\Money::tnd((int) ($expr))); ?>");
    }
}
