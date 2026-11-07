<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter;

use InvalidArgumentException;
use Pkg6\CloudPrint\Contracts\ClientInterface;
use Pkg6\CloudPrint\Requests\PrintRequest;
use Psr\SimpleCache\CacheInterface;
use think\App;

class Printer
{
    protected array $clientTable = [
        'feieyun'   => \Pkg6\CloudPrint\Feieyun\Client::class,
        'jolimark'  => \Pkg6\CloudPrint\Jolimark\Client::class,
        'kuaidi100' => \Pkg6\CloudPrint\Kuaidi100\Client::class,
        'poscom'    => \Pkg6\CloudPrint\Poscom\Client::class,
        'ushengyun' => \Pkg6\CloudPrint\Ushengyun\Client::class,
        'xpyun'     => \Pkg6\CloudPrint\Xpyun\Client::class,
        'yilianyun' => \Pkg6\CloudPrint\Yilianyun\Client::class,
        'zhongwuyun' => \Pkg6\CloudPrint\Zhongwuyun\Client::class,
    ];

    protected array $clients = [];

    protected array $extensions = [];

    protected array $config;

    public function __construct(protected App $app, array $config = [])
    {
        $this->config = array_replace_recursive(static::defaultConfig(), $config);
    }

    public static function defaultConfig(): array
    {
        return require __DIR__ . '/../config/printer.php';
    }

    public function client(?string $name = null): ClientInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if ($name === '') {
            throw new InvalidArgumentException('Cloud printer driver name cannot be empty.');
        }

        return $this->clients[$name] ??= $this->createClient($name);
    }

    public function driver(?string $name = null): ClientInterface
    {
        return $this->client($name);
    }

    public function connect(?string $name = null): ClientInterface
    {
        return $this->client($name);
    }

    public function extend(string $name, ClientInterface|callable $client): static
    {
        $this->extensions[$name] = $client;
        unset($this->clients[$name]);

        return $this;
    }

    public function forget(?string $name = null): static
    {
        if ($name === null) {
            $this->clients = [];
        } else {
            unset($this->clients[$name]);
        }

        return $this;
    }

    public function getDefaultDriver(): string
    {
        return (string) ($this->config['default'] ?? '');
    }

    public function getClients(): array
    {
        return $this->config['clients'] ?? [];
    }

    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->config;
        }

        return $this->config[$name] ?? $default;
    }

    public function getClientConfig(?string $name = null): array
    {
        $name ??= $this->getDefaultDriver();
        $config = $this->config['clients'][$name] ?? null;

        if (!is_array($config)) {
            throw new InvalidArgumentException("Cloud printer [$name] is not configured.");
        }

        return $config;
    }

    public function resolveClass(string $type): string
    {
        if (class_exists($type) && is_subclass_of($type, ClientInterface::class)) {
            return $type;
        }

        if (isset($this->clientTable[$type])) {
            return $this->clientTable[$type];
        }

        throw new InvalidArgumentException("Cloud printer driver [$type] is not supported.");
    }

    public function print(PrintRequest $request): string
    {
        return $this->client()->print($request);
    }

    public function __call(string $method, array $arguments): mixed
    {
        return $this->client()->{$method}(...$arguments);
    }

    protected function createClient(string $name): ClientInterface
    {
        if (isset($this->extensions[$name])) {
            $client = $this->makeExtendedClient($name);
        } else {
            $config = $this->getClientConfig($name);
            $type = (string) ($config['type'] ?? $name);
            unset($config['type']);

            $class = $this->resolveClass($type);
            $client = new $class($config);
        }

        if (!$client instanceof ClientInterface) {
            throw new InvalidArgumentException("Cloud printer [$name] must implement " . ClientInterface::class . '.');
        }

        $this->configure($client);

        return $client;
    }

    protected function makeExtendedClient(string $name): ClientInterface
    {
        $extension = $this->extensions[$name];

        if ($extension instanceof ClientInterface) {
            return $extension;
        }

        return $extension($this->config['clients'][$name] ?? [], $this->app);
    }

    protected function configure(ClientInterface $client): void
    {
        if (($this->config['cache']['enabled'] ?? false) === true) {
            $client->setCache($this->resolveCache());
        }

        if (($this->config['log']['enabled'] ?? false) === true) {
            $client->setRequestLogger($this->app->log);
            $client->enabledRequestLog();
        }
    }

    protected function resolveCache(): CacheInterface
    {
        $store = $this->config['cache']['store'] ?? null;
        $cache = $this->app->cache->store($store ?: null);

        if (!$cache instanceof CacheInterface) {
            throw new InvalidArgumentException('The configured cache store must implement ' . CacheInterface::class . '.');
        }

        return $cache;
    }
}