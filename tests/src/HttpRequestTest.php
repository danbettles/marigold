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

        $this->assertSame($_GET, $request->query);
        $this->assertSame($_POST, $request->request);
        $this->assertSame($_SERVER, $request->server);
        $this->assertSame([], $request->attributes);
    }

    public function testCreatefromglobalsCreatesANewInstance(): void
    {
        $request = HttpRequest::createFromGlobals();

        $this->assertInstanceOf(HttpRequest::class, $request);
        $this->assertSame($_GET, $request->query);
        $this->assertSame($_POST, $request->request);
        $this->assertSame($_SERVER, $request->server);
        $this->assertSame([], $request->attributes);
    }
}
