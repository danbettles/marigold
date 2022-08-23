<?php

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Tests\AbstractTestCaseTest;

require __DIR__ . '/../../vendor/autoload.php';

(function () {
    $classAtRootOfTests = new ReflectionClass(AbstractTestCaseTest::class);

    AbstractTestCase::$testsNamespace = $classAtRootOfTests->getNamespaceName();
    AbstractTestCase::$testsDir = dirname($classAtRootOfTests->getFileName());
})();
