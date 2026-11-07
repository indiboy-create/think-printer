<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter\Tests;

use Pkg6\CloudPrint\Feieyun\Client as FeieyunClient;
use Youvision\ThinkPrinter\facade\Printer as PrinterFacade;
use Youvision\ThinkPrinter\Printer;
use Youvision\ThinkPrinter\Service;

final class ServiceTest extends TestCase
{
    public function testServiceRegistersSharedPrinter(): void
    {
        $this->app->config->set([
            'default' => 'feieyun',
            'clients' => [
                'feieyun' => ['type' => 'feieyun', 'user' => 'user', 'ukey' => 'ukey'],
            ],
            'cache' => ['enabled' => false],
            'log' => ['enabled' => false],
        ], 'printer');

        $this->app->register(Service::class);

        $printer = $this->app->make('printer');

        $this->assertInstanceOf(Printer::class, $printer);
        $this->assertSame($printer, $this->app->make(Printer::class));
        $this->assertSame($printer, $this->app->make('printer'));
        $this->assertInstanceOf(FeieyunClient::class, $printer->client());
    }

    public function testFacadeResolvesRegisteredPrinter(): void
    {
        $this->app->config->set([
            'default' => 'zhongwuyun',
            'clients' => [
                'zhongwuyun' => ['type' => 'zhongwuyun', 'appid' => 'appid', 'appsecret' => 'secret'],
            ],
            'cache' => ['enabled' => false],
            'log' => ['enabled' => false],
        ], 'printer');

        $this->app->register(Service::class);

        $this->assertSame('zhongwuyun', PrinterFacade::getDefaultDriver());
        $this->assertSame('zhongwuyun', PrinterFacade::getClientConfig()['type']);
    }
}