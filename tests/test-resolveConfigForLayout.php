<?php

namespace ACFComposer\Tests;

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use Exception;
use Brain\Monkey\WP\Filters;
use ACFComposer\ResolveConfig;

class ResolveConfigForLayoutTest extends TestCase
{
    public function testForLayoutWithValidConfig()
    {
        $config = [
            'name' => 'someLayout',
            'label' => 'Some Layout'
        ];
        $output = ResolveConfig::forLayout($config);
        $config['key'] = 'field_someLayout';
        $this->assertEquals($config, $output);
    }

    public function testForLayoutFailsWithoutName()
    {
        $config = [
            'label' => 'Some Layout'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLayout($config);
    }

    public function testForLayoutFailsWithoutLabel()
    {
        $config = [
            'name' => 'someLayout'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLayout($config);
    }

    public function testForLayoutFailsWithKey()
    {
        $config = [
            'name' => 'someLayout',
            'label' => 'Some Layout',
            'key' => 'someKey'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLayout($config);
    }

    public function testForLayoutGetConfigFromFilter()
    {
        $config = 'ACFComposer/Layout/someLayout';
        $someLayout = [
            'name' => 'someLayout',
            'label' => 'Some Layout'
        ];
        Filters::expectApplied($config)
            ->once()
            ->andReturn($someLayout);
        $output = ResolveConfig::forLayout($config);
        $someLayout['key'] = 'field_someLayout';
        $this->assertEquals($someLayout, $output);
    }
    public function testForLayoutWithValidSubField()
    {
        $subFieldConfig = [
            'name' => 'subField',
            'label' => 'Sub Field',
            'type' => 'someType'
        ];
        $config = [
            'name' => 'someLayout',
            'label' => 'Some Layout',
            'sub_fields' => [$subFieldConfig]
        ];
        $output = ResolveConfig::forLayout($config);
        $subFieldConfig['key'] = 'field_someLayout_subField';
        $this->assertEquals($subFieldConfig, $output['sub_fields'][0]);
    }

    public function testForLayoutFailWithInvalidSubField()
    {
        $subFieldConfig = [
            'name' => 'subField',
            'label' => 'Sub Field'
        ];
        $config = [
            'name' => 'someLayout',
            'label' => 'Some Layout',
            'sub_fields' => [$subFieldConfig]
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forLayout($config);
    }
}
