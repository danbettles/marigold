<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpRequest;

class HttpRequestTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $request = new HttpRequest($_GET, $_POST, $_SERVER);

        $this->assertSame($request->query, $_GET);
        $this->assertSame($request->request, $_POST);
        $this->assertSame($request->server, $_SERVER);
    }

    public function testFromglobalsCreatesANewInstance(): void
    {
        $request = HttpRequest::fromGlobals();

        $this->assertInstanceOf(HttpRequest::class, $request);
        $this->assertSame($request->query, $_GET);
        $this->assertSame($request->request, $_POST);
        $this->assertSame($request->server, $_SERVER);
    }
}
