<?php

namespace ACFComposer\Tests;

use PHPUnit\Framework;
use Brain\Monkey;

class TestCase extends Framework\TestCase
{

    protected function setUp()
    {
        parent::setUp();
        Monkey::setUpWP();
    }

    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }
}
