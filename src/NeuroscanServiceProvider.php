<?php

namespace Rir\Neuroscan;

use Rir\Neuroscan\Console\NeuroscanParseCommand;
use Illuminate\Support\ServiceProvider;

class NeuroscanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/neuroscan.php' => config_path('neuroscan.php')
        ]);

        $this->commands([
            NeuroscanParseCommand::class
        ]);
    }
}
