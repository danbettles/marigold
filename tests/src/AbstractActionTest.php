<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractAction;
use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\NotFoundHttpException;
use DanBettles\Marigold\HttpRequest;
use DanBettles\Marigold\HttpResponse;
use DanBettles\Marigold\TemplateEngine\Engine;
use DanBettles\Marigold\TemplateEngine\TemplateFileLoader;
use ReflectionNamedType;

class AbstractActionTest extends AbstractTestCase
{
    public function testIsAbstract(): void
    {
        $this->assertTrue($this->getTestedClass()->isAbstract());
    }

    public function testIsConstructedWithATemplateEngine(): void
    {
        $engineStub = $this->createStub(Engine::class);

        $action = $this->getMockForAbstractClass(AbstractAction::class, [
            'templateEngine' => $engineStub,
        ]);

        $this->assertSame($engineStub, $action->getTemplateEngine());
    }

    public function testIsInvokable(): void
    {
        $invoke = $this->getTestedClass()->getMethod('__invoke');

        $this->assertTrue($invoke->isAbstract());
        $this->assertTrue($invoke->isPublic());

        $this->assertSame(1, $invoke->getNumberOfParameters());
        $this->assertSame(1, $invoke->getNumberOfRequiredParameters());

        $theParam = $invoke->getParameters()[0];
        /** @var ReflectionNamedType */
        $theParamType = $theParam->getType();

        $this->assertInstanceOf(ReflectionNamedType::class, $theParamType);
        $this->assertSame(HttpRequest::class, $theParamType->getName());

        /** @var ReflectionNamedType */
        $returnType = $invoke->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame(HttpResponse::class, $returnType->getName());
    }

    public function testRenderExecutesATemplateFileAndReturnsAnHttpResponse(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $templateFileLoader = new TemplateFileLoader([$fixturesDir]);
        $templateEngine = Engine::create($templateFileLoader);

        $action = new class ($templateEngine) extends AbstractAction
        {
            public function __invoke(HttpRequest $request): HttpResponse
            {
                return $this->render('error.php', [
                    'name' => 'Dave',
                ], 418);
            }
        };

        $response = $action(HttpRequest::createFromGlobals());

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame(418, $response->getStatusCode());
        $this->assertSame("I'm sorry, Dave, I'm afraid I can't do that.", $response->getContent());
    }

    public function testCreatenotfoundexceptionCreatesANotFoundHttpexception(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('404 Not Found: something');

        $templateEngineStub = $this->createStub(Engine::class);

        (new class ($templateEngineStub) extends AbstractAction
        {
            public function __invoke(HttpRequest $request): HttpResponse
            {
                throw $this->createNotFoundException('something');
            }
        })(HttpRequest::createFromGlobals());
    }
}
