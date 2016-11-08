<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveConfigForLayoutTest extends TestCase {
  function testForLayoutWithValidConfig() {
    $config = [
      'name' => 'someLayout',
      'label' => 'Some Layout'
    ];
    $output = ResolveConfig::forLayout($config);
    $this->assertEquals($config, $output);
  }

  function testForLayoutFailsWithoutName() {
    $config = [
      'label' => 'Some Layout'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutFailsWithoutLabel() {
    $config = [
      'name' => 'someLayout'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutFailsWithKey() {
    $config = [
      'name' => 'someLayout',
      'label' => 'Some Layout',
      'key' => 'someKey'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutGetConfigFromFilter() {
    $config = 'ACFComposer/Layout/someLayout';
    $someLayout = [
      'name' => 'someLayout',
      'label' => 'Some Layout'
    ];
    Filters::expectApplied($config)
    ->once()
    ->andReturn($someLayout);
    $output = ResolveConfig::forLayout($config);
    $this->assertEquals($someLayout, $output);
  }
  function testForLayoutWithValidSubField() {
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
    $this->assertEquals($subFieldConfig, $output['sub_fields'][0]);
  }

  function testForLayoutFailWithInvalidSubField() {
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
