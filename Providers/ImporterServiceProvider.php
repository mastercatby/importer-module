<?php

namespace App\Modules\Importer\Providers;
use App\Modules\Importer\Console\Commands\ImporterLocalFile;
use Illuminate\Support\ServiceProvider;

class ImporterServiceProvider extends ServiceProvider
{
    /**
     * Register the ExternalServices module service provider.
     *
     * @return void
     */
    public function register()
    {
        // This service provider is a convenient place to register your modules
        // services in the IoC container. If you wish, you may make additional
        // methods or service providers to keep the code more focused and granular.

        //$this->registerCommands();
    }

    /**
     * Bootstrap application services
     *
     * @return void
     */
    public function boot()
    {

        if(is_dir(__DIR__.'/../Resources/views')) {
            $this->loadViewsFrom(__DIR__.'/../Resources/views', 'importer');
        }
    
    }


    private function registerCommands()
    {
        $this->commands([
            ImporterLocalFile::class
        ]);
    }

}
