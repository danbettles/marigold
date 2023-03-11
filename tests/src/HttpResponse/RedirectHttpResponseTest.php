<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\HttpResponse;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpResponse;
use DanBettles\Marigold\HttpResponse\RedirectHttpResponse;
use InvalidArgumentException;

class RedirectHttpResponseTest extends AbstractTestCase
{
    public function testIsAnHttpresponse(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(HttpResponse::class));
    }

    public function testIsInstantiable(): void
    {
        $basicRedirect = new RedirectHttpResponse(
            'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302'
        );

        $this->assertSame(
            'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302',
            $basicRedirect->getTargetUrl()
        );

        $this->assertSame(302, $basicRedirect->getStatusCode());

        $this->assertSame(<<<END
        <html>
        <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302'" />
        <title>Redirecting to https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302</title>
        </head>
        <body>Redirecting to <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302">https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302</a></body>
        </html>
        END, $basicRedirect->getContent());

        $this->assertEquals([
            'Location' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302',
        ], $basicRedirect->getHeaders());

        $redirectWithADifferentStatusCode = new RedirectHttpResponse(
            'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303',
            303
        );

        $this->assertSame(
            'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303',
            $redirectWithADifferentStatusCode->getTargetUrl()
        );

        $this->assertSame(303, $redirectWithADifferentStatusCode->getStatusCode());

        $this->assertSame(<<<END
        <html>
        <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303'" />
        <title>Redirecting to https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303</title>
        </head>
        <body>Redirecting to <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303">https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303</a></body>
        </html>
        END, $redirectWithADifferentStatusCode->getContent());

        $this->assertEquals([
            'Location' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303',
        ], $redirectWithADifferentStatusCode->getHeaders());

        $redirectWithAdditionalHeaders = new RedirectHttpResponse(
            'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303',
            303,
            ['X-Powered-By' => 'Marigold']
        );

        $this->assertEquals([
            'Location' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303',
            'X-Powered-By' => 'Marigold',
        ], $redirectWithAdditionalHeaders->getHeaders());
    }

    public function testOverwritesACustomLocationHeader(): void
    {
        $redirect = new RedirectHttpResponse('https://example.com', 302, [
            'Location' => 'https://bbc.com/news',
        ]);

        $this->assertSame('https://example.com', $redirect->getTargetUrl());
    }

    public function testTheTargetUrlCanBeChanged(): void
    {
        $oldTargetUrl = 'http://example.com';
        $newTargetUrl = 'https://www.example.com';

        $this->assertNotSame($oldTargetUrl, $newTargetUrl);

        $redirect = new RedirectHttpResponse($oldTargetUrl);
        $something = $redirect->setTargetUrl($newTargetUrl);

        $this->assertSame($newTargetUrl, $redirect->getTargetUrl());
        $this->assertSame($redirect, $something);

        $this->assertSame(<<<END
        <html>
        <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='{$newTargetUrl}'" />
        <title>Redirecting to {$newTargetUrl}</title>
        </head>
        <body>Redirecting to <a href="{$newTargetUrl}">{$newTargetUrl}</a></body>
        </html>
        END, $redirect->getContent());

        $this->assertSame(302, $redirect->getStatusCode());

        $this->assertEquals([
            'Location' => $newTargetUrl,
        ], $redirect->getHeaders());
    }

    public function testThrowsAnExceptionIfTheStatusCodeDoesNotIdentifyARedirect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The status code does not identify a redirect');

        new RedirectHttpResponse('https://example.com', 200);
    }

    public function testSetstatuscodeThrowsAnExceptionIfTheStatusCodeDoesNotIdentifyARedirect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The status code does not identify a redirect');

        (new RedirectHttpResponse('https://example.com'))
            ->setStatusCode(404)
        ;
    }

    public function testSetstatuscodeReturnsTheHttpresponseInstance(): void
    {
        $redirect = new RedirectHttpResponse('https://example.com');
        $something = $redirect->setStatusCode(303);

        $this->assertSame($redirect, $something);
    }
}
