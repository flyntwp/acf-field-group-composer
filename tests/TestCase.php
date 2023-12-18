<?php

namespace ACFComposer\Tests;

use PHPUnit\Framework;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class TestCase extends Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp() : void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown() : void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}
