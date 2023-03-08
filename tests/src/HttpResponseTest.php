<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpRequest;
use DanBettles\Marigold\HttpResponse;

class HttpResponseTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $emptyResponse = new HttpResponse();
        $this->assertSame('', $emptyResponse->getContent());
        $this->assertSame(200, $emptyResponse->getStatusCode());
        $this->assertEmpty($emptyResponse->getHeaders());

        $successResponse = new HttpResponse('foo');
        $this->assertSame('foo', $successResponse->getContent());
        $this->assertSame(200, $successResponse->getStatusCode());
        $this->assertEmpty($successResponse->getHeaders());

        $notFoundResponse = new HttpResponse('bar', 404);
        $this->assertSame('bar', $notFoundResponse->getContent());
        $this->assertSame(404, $notFoundResponse->getStatusCode());
        $this->assertEmpty($notFoundResponse->getHeaders());

        $internalServerErrorResponse = new HttpResponse('baz', 500);
        $this->assertSame('baz', $internalServerErrorResponse->getContent());
        $this->assertSame(500, $internalServerErrorResponse->getStatusCode());
        $this->assertEmpty($internalServerErrorResponse->getHeaders());

        // Temporary, probably:
        $responseWithUnknownStatus = new HttpResponse('qux', 0);
        $this->assertSame('qux', $responseWithUnknownStatus->getContent());
        $this->assertSame(0, $responseWithUnknownStatus->getStatusCode());
        $this->assertEmpty($responseWithUnknownStatus->getHeaders());

        $responseWithCustomHeaders = new HttpResponse('quux', 302, ['Location' => 'https://example.com/']);
        $this->assertSame('quux', $responseWithCustomHeaders->getContent());
        $this->assertSame(302, $responseWithCustomHeaders->getStatusCode());
        $this->assertEquals(['Location' => 'https://example.com/'], $responseWithCustomHeaders->getHeaders());
    }

    public function testSendEmitsTheResponse(): void
    {
        $content = '{"status":"error","message":"An unexpected error occurred"}';

        $this->expectOutputString($content);

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->onlyMethods(['sendHeader'])
            ->setConstructorArgs([
                $content,
                500,
                ['Content-Type' => 'application/json']
            ])
            ->getMock()
        ;

        $responseMock
            ->expects($this->exactly(2))
            ->method('sendHeader')
            ->withConsecutive(
                ['Content-Type', 'application/json'],
                ['HTTP/1.1 500 Internal Server Error']
            )
        ;

        $request = new HttpRequest([], [], [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);

        /** @var HttpResponse $responseMock */
        $responseMock->send($request);
    }

    public function testSendWillEmitAResponseWithAnUnknownStatus(): void
    {
        $this->expectOutputString('Foo!');

        $responseMock = $this
            ->getMockBuilder(HttpResponse::class)
            ->onlyMethods(['sendHeader'])
            ->setConstructorArgs([
                'Foo!',
                0
            ])
            ->getMock()
        ;

        $responseMock
            ->expects($this->once())
            ->method('sendHeader')
            ->with('HTTP/1.1 0')
        ;

        $request = new HttpRequest([], [], [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);

        /** @var HttpResponse $responseMock */
        $responseMock->send($request);
    }

    public function testPropertiesHaveSetters(): void
    {
        $getsStatusCode = (new HttpResponse())
            ->setStatusCode(500)
        ;

        $this->assertSame(500, $getsStatusCode->getStatusCode());

        $getsContent = (new HttpResponse())
            ->setContent('Foo')
        ;

        $this->assertSame('Foo', $getsContent->getContent());

        $getsHeaders = (new HttpResponse())
            ->setHeaders(['Content-Type' => 'text/html'])
        ;

        $this->assertSame(['Content-Type' => 'text/html'], $getsHeaders->getHeaders());
    }
}
