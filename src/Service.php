<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter;

use think\Service as ThinkService;

class Service extends ThinkService
{
    public function register(): void
    {
        $this->app->bind('printer', function () {
            return new Printer($this->app, (array) $this->app->config->get('printer', []));
        });

        $this->app->bind(Printer::class, function () {
            return $this->app->make('printer');
        });
    }
}