<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpRequest;

class HttpRequestTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $requestWithoutContent = new HttpRequest($_GET, $_POST, $_SERVER);

        $this->assertSame($_GET, $requestWithoutContent->query);
        $this->assertSame($_POST, $requestWithoutContent->request);
        $this->assertSame($_SERVER, $requestWithoutContent->server);
        $this->assertSame([], $requestWithoutContent->attributes);
        $this->assertNull($requestWithoutContent->getContent());  // `null` for now.

        $requestWithContent = new HttpRequest($_GET, $_POST, $_SERVER, 'foo');

        $this->assertSame('foo', $requestWithContent->getContent());
    }

    public function testCreatefromglobalsCreatesANewInstance(): void
    {
        $request = HttpRequest::createFromGlobals();

        $this->assertInstanceOf(HttpRequest::class, $request);
        $this->assertSame($_GET, $request->query);
        $this->assertSame($_POST, $request->request);
        $this->assertSame($_SERVER, $request->server);
        $this->assertSame([], $request->attributes);
        $this->assertNull($request->getContent());  // `null` for now.
    }

    public function testContentIsNotDirectlyAccessible(): void
    {
        $contentProperty = $this->getTestedClass()->getProperty('content');

        $this->assertTrue($contentProperty->isPrivate());
    }

    public function testContentAccessors(): void
    {
        $request = new HttpRequest([], [], []);
        $something = $request->setContent('bar');

        $this->assertSame('bar', $request->getContent());
        $this->assertSame($request, $something);
    }
}
