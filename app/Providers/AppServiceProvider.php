<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('pdfThaana', function (string $expression) {
            return "<?php echo \\App\\Support\\PdfThaana::html({$expression}); ?>";
        });

        Blade::directive('pdfThaanaIndicator', function (string $expression) {
            return "<?php echo \\App\\Support\\PdfThaana::html({$expression}, true, 'indicator'); ?>";
        });

        Blade::directive('pdfText', function (string $expression) {
            return "<?php echo \\App\\Support\\PdfThaana::html({$expression}, false); ?>";
        });

        \Illuminate\Support\Facades\Route::bind('key_behavior', fn (string $value) => \App\Models\AppraisalFormKeyBehavior::findOrFail($value));
        \Illuminate\Support\Facades\Route::bind('assignee', fn (string $value) => \App\Models\HodFormAssignee::findOrFail($value));
        \Illuminate\Support\Facades\Route::bind('hodAssignment', fn (string $value) => \App\Models\FormsAssignedToHod::findOrFail($value));
        \Illuminate\Support\Facades\Route::bind('assignment', fn (string $value) => \App\Models\AppraisalFormAssignedToStaff::findOrFail($value));
        \Illuminate\Support\Facades\Route::bind('role', fn (string $value) => \Spatie\Permission\Models\Role::findOrFail($value));
    }
}
