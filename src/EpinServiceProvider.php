<?php

namespace Aiomlm\Epin;
use Illuminate\Support\ServiceProvider;
/**
 *
 */
class EpinServiceProvider extends ServiceProvider
{

   public function boot()
   {
        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
        }
   }

   public function register()
   {

   }
}
