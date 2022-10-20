<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpResponse;

class HttpResponseTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $emptyResponse = new HttpResponse();
        $this->assertSame('', $emptyResponse->getContent());
        $this->assertSame(200, $emptyResponse->getStatusCode());

        $emptyResponse = new HttpResponse('foo');
        $this->assertSame('foo', $emptyResponse->getContent());
        $this->assertSame(200, $emptyResponse->getStatusCode());

        $emptyResponse = new HttpResponse('bar', 404);
        $this->assertSame('bar', $emptyResponse->getContent());
        $this->assertSame(404, $emptyResponse->getStatusCode());

        $emptyResponse = new HttpResponse('baz', 500);
        $this->assertSame('baz', $emptyResponse->getContent());
        $this->assertSame(500, $emptyResponse->getStatusCode());
    }

    public function testSendSendsTheResponseToTheClient(): void
    {
        $this->expectOutputString('An unexpected error occurred.');

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->onlyMethods(['sendHeader'])
            ->setConstructorArgs([
                'An unexpected error occurred.',
                500
            ])
            ->getMock()
        ;

        $responseMock
            ->expects($this->once())
            ->method('sendHeader')
            ->with('HTTP/1.1 500 Internal Server Error')
        ;

        /** @var HttpResponse $responseMock */
        $responseMock->send([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);
    }

    public function testPropertiesHaveSetters(): void
    {
        $getsStatusCode = new HttpResponse();
        $getsStatusCode->setStatusCode(500);

        $this->assertSame(500, $getsStatusCode->getStatusCode());

        $getsContent = new HttpResponse();
        $getsContent->setContent('Foo');

        $this->assertSame('Foo', $getsContent->getContent());
    }
}
