<?php

namespace ACFComposer\Tests;

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use Exception;
use Brain\Monkey\WP\Filters;
use ACFComposer\ResolveConfig;

class ResolveConfigForLocationTest extends TestCase
{
    public function testForLocationWithValidConfig()
    {
        $config = [
            'param' => 'someParam',
            'operator' => 'someOperator',
            'value' => 'someValue'
        ];
        $output = ResolveConfig::forLocation($config);
        $this->assertEquals($config, $output);
    }

    public function testForLocationFailsWithoutParam()
    {
        $config = [
            'operator' => 'someOperator',
            'value' => 'someValue'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLocation($config);
    }

    public function testForLocationFailsWithoutOperator()
    {
        $config = [
            'param' => 'someParam',
            'value' => 'someValue'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLocation($config);
    }

    public function testForLocationFailsWithoutValue()
    {
        $config = [
            'param' => 'someParam',
            'operator' => 'someOperator',
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLocation($config);
    }
}
