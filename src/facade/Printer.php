<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter\facade;

use Pkg6\CloudPrint\Contracts\ClientInterface;
use think\Facade;
use Youvision\ThinkPrinter\Printer as PrinterManager;

/**
 * @see PrinterManager
 * @mixin PrinterManager
 *
 * @method static ClientInterface client(?string $name = null)
 * @method static ClientInterface driver(?string $name = null)
 * @method static ClientInterface connect(?string $name = null)
 * @method static PrinterManager extend(string $name, ClientInterface|callable $client)
 * @method static PrinterManager forget(?string $name = null)
 * @method static string getDefaultDriver()
 * @method static array getClients()
 * @method static array getClientConfig(?string $name = null)
 * @method static mixed getConfig(?string $name = null, mixed $default = null)
 * @method static string resolveClass(string $type)
 * @method static string print(\Pkg6\CloudPrint\Requests\PrintRequest $request)
 */
class Printer extends Facade
{
    protected static function getFacadeClass()
    {
        return 'printer';
    }
}