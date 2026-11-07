<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter\Tests;

use InvalidArgumentException;
use Pkg6\CloudPrint\Feieyun\Client as FeieyunClient;
use Pkg6\CloudPrint\Xpyun\Client as XpyunClient;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Youvision\ThinkPrinter\Printer;

final class PrinterTest extends TestCase
{
    public function testClientUsesDefaultDriver(): void
    {
        $printer = new Printer($this->app, [
            'default' => 'feieyun',
            'clients' => [
                'feieyun' => ['type' => 'feieyun', 'user' => 'user', 'ukey' => 'ukey'],
            ],
            'cache' => ['enabled' => false],
        ]);

        $this->assertInstanceOf(FeieyunClient::class, $printer->client());
    }

    public function testSameDriverSupportsMultipleConnections(): void
    {
        $printer = new Printer($this->app, [
            'default' => 'shop_a',
            'clients' => [
                'shop_a' => ['type' => 'feieyun', 'user' => 'a', 'ukey' => 'a'],
                'shop_b' => ['type' => 'feieyun', 'user' => 'b', 'ukey' => 'b'],
            ],
            'cache' => ['enabled' => false],
        ]);

        $this->assertInstanceOf(FeieyunClient::class, $printer->client('shop_a'));
        $this->assertInstanceOf(FeieyunClient::class, $printer->client('shop_b'));
        $this->assertNotSame($printer->client('shop_a'), $printer->client('shop_b'));
    }

    public function testDriverTypeCanBeConcreteClass(): void
    {
        $printer = new Printer($this->app, [
            'default' => 'custom',
            'clients' => [
                'custom' => ['type' => XpyunClient::class, 'user' => 'user', 'userKey' => 'key'],
            ],
            'cache' => ['enabled' => false],
        ]);

        $this->assertInstanceOf(XpyunClient::class, $printer->client());
    }

    public function testUnsupportedDriverThrows(): void
    {
        $printer = new Printer($this->app, [
            'default' => 'feieyun',
            'clients' => [
                'feieyun' => ['type' => 'unknown'],
            ],
            'cache' => ['enabled' => false],
        ]);

        $this->expectException(InvalidArgumentException::class);

        $printer->client();
    }

    public function testUnconfiguredConnectionThrows(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => false],
        ]);

        $this->expectException(InvalidArgumentException::class);

        $printer->client('not_exists');
    }

    public function testClientInstanceIsReused(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => false],
        ]);

        $this->assertSame($printer->client('feieyun'), $printer->client('feieyun'));
        $this->assertSame($printer->client('feieyun'), $printer->driver('feieyun'));
    }

    public function testExtendWithClientInstance(): void
    {
        $client = new FeieyunClient(['user' => 'user', 'ukey' => 'ukey']);
        $printer = new Printer($this->app, ['cache' => ['enabled' => false]]);

        $printer->extend('custom', $client);

        $this->assertSame($client, $printer->client('custom'));
    }

    public function testExtendWithFactory(): void
    {
        $printer = new Printer($this->app, [
            'clients' => [
                'custom' => ['type' => 'feieyun', 'user' => 'user', 'ukey' => 'ukey'],
            ],
            'cache' => ['enabled' => false],
        ]);

        $printer->extend('custom', function (array $config) {
            return new XpyunClient($config);
        });

        $this->assertInstanceOf(XpyunClient::class, $printer->client('custom'));
    }

    public function testCacheIsInjectedWhenEnabled(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => true],
        ]);

        $client = $printer->client('yilianyun');

        $this->assertInstanceOf(CacheInterface::class, $this->property($client, 'cache'));
    }

    public function testCacheIsNotInjectedWhenDisabled(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => false],
        ]);

        $client = $printer->client('yilianyun');

        $this->assertNull($this->property($client, 'cache'));
    }

    public function testLogIsInjectedWhenEnabled(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => false],
            'log' => ['enabled' => true],
        ]);

        $client = $printer->client('feieyun');

        $this->assertInstanceOf(LoggerInterface::class, $this->property($client, 'logger'));
        $this->assertTrue($this->property($client, 'logRequests'));
    }

    public function testLogIsNotInjectedWhenDisabled(): void
    {
        $printer = new Printer($this->app, [
            'cache' => ['enabled' => false],
            'log' => ['enabled' => false],
        ]);

        $client = $printer->client('feieyun');

        $this->assertNull($this->property($client, 'logger'));
        $this->assertFalse($this->property($client, 'logRequests'));
    }

    public function testDefaultConfigIsMergedWithUserConfig(): void
    {
        $printer = new Printer($this->app, [
            'default' => 'zhongwuyun',
            'clients' => [
                'zhongwuyun' => ['appid' => 'appid', 'appsecret' => 'appsecret'],
            ],
        ]);

        $config = $printer->getClientConfig();

        $this->assertSame('zhongwuyun', $printer->getDefaultDriver());
        $this->assertSame('zhongwuyun', $config['type']);
        $this->assertSame('appid', $config['appid']);
        $this->assertArrayHasKey('feieyun', $printer->getClients());
    }
}