<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveFieldConfigForLayoutTest extends TestCase {
  function testForLayoutWithValidConfig() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field'
    ];
    $output = ResolveConfig::forLayout($config);
    $this->assertEquals($config, $output);
  }

  function testForLayoutFailsWithoutName() {
    $config = [
      'label' => 'Some Field'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutFailsWithoutLabel() {
    $config = [
      'name' => 'someField'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutFailsWithKey() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'key' => 'someKey'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLayout($config);
  }

  function testForLayoutGetConfigFromFilter() {
    $config = 'ACFComposer/Fields/someField';
    $someField = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    Filters::expectApplied($config)
    ->once()
    ->andReturn($someField);
    $output = ResolveConfig::forLayout($config);
    $this->assertEquals($someField, $output);
  }
}
