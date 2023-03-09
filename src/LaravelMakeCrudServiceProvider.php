<?php

namespace LanciWeb\LaravelMakeCrud;

use Illuminate\Support\ServiceProvider;
use LanciWeb\LaravelMakeCrud\Commands\MakeCrudCommand;

class LaravelMakeCrudServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->commands([MakeCrudCommand::class]);
    }
  }
}
