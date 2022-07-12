<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Service;

use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\Service\TemplatingService;
use DanBettles\Marigold\Tests\AbstractTestCase;

use function spl_object_id;

class TemplatingServiceTest extends AbstractTestCase
{
    public function testConstructor()
    {
        $config = [];
        $service = new TemplatingService($config);

        $this->assertEquals($config, $service->getConfig());
    }

    public function testDoesNotRequireConfig()
    {
        $service = new TemplatingService();

        $this->assertInstanceOf(TemplatingService::class, $service);
    }

    public function testRenderPassesAllTemplateVarsToTheTemplateInAnArray()
    {
        $service = new TemplatingService();

        $output = $service->render($this->createFixturePathname('contains_var_variables.php'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertSame(<<<'END'
        Array
        (
            [foo] => bar
            [baz] => qux
        )

        END, $output);
    }

    public function testRenderDoesNotRequireVars()
    {
        $service = new TemplatingService();
        $output = $service->render($this->createFixturePathname('hello_world.txt'));

        $this->assertSame('Hello, world!', $output);
    }

    public function testRenderPassesTheTemplatingServiceToTheTemplate()
    {
        $service = new TemplatingService();
        $output = $service->render($this->createFixturePathname('contains_var_service.php'), []);

        $this->assertSame((string) spl_object_id($service), $output);
    }

    public function testRenderPassesAnAppropriateOutputHelperToTheTemplateIfItsNameFollowsTheConvention()
    {
        $outputHelperStub = $this->createStub(OutputHelperInterface::class);

        $service = new TemplatingService([
            'output_helpers' => [
                'html' => $outputHelperStub,
            ],
        ]);

        $output = $service->render($this->createFixturePathname('contains_var_helper.html.php'), []);

        $this->assertSame((string) spl_object_id($outputHelperStub), $output);
    }

    public function testUsesTheTemplatesDirConfigIfSet()
    {
        $service = new TemplatingService([
            'templates_dir' => $this->getFixturesDir(),
        ]);

        $output = $service->render('hello_world.txt');

        $this->assertSame('Hello, world!', $output);
    }
}
