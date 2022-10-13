<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractAction;
use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpRequest;
use DanBettles\Marigold\HttpResponse;
use DanBettles\Marigold\TemplateEngine\Engine;
use ReflectionNamedType;
use ReflectionType;

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
}
