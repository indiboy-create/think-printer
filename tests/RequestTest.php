<?php

declare(strict_types=1);

namespace Youvision\ThinkPrinter\Tests;

use Pkg6\CloudPrint\Requests\PrintRequest;
use Youvision\ThinkPrinter\Request;

final class RequestTest extends TestCase
{
    public function testRequestExtendsCloudPrintRequest(): void
    {
        $request = Request::create()
            ->sn('99999999')
            ->content('订单内容')
            ->copies(2)
            ->orderId('order-1')
            ->type('label');

        $this->assertInstanceOf(PrintRequest::class, $request);
        $this->assertSame([
            'sn' => '99999999',
            'content' => '订单内容',
            'copies' => 2,
            'orderId' => 'order-1',
            'retry' => 0,
            'type' => 'label',
            'extra' => [],
        ], $request->toArray());
    }
}