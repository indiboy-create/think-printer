<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use think\App;

abstract class TestCase extends PHPUnitTestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new App(dirname(__DIR__));

        $this->app->config->set([
            'default' => 'file',
            'stores' => [
                'file' => [
                    'type' => 'File',
                    'path' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'think-printer-tests' . DIRECTORY_SEPARATOR,
                ],
            ],
        ], 'cache');

        $this->app->config->set([
            'default' => 'file',
            'channels' => [
                'file' => [
                    'type' => 'File',
                    'path' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'think-printer-tests' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
                ],
            ],
        ], 'log');
    }

    protected function property(object $object, string $property): mixed
    {
        return (new \ReflectionProperty($object, $property))->getValue($object);
    }
}