<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveConfigForFieldTest extends TestCase {
  function testForFieldWithValidConfig() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $output = ResolveConfig::forField($config);
    $this->assertEquals($config, $output);
  }

  function testForFieldFailsWithoutName() {
    $config = [
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forField($config);
  }

  function testForFieldFailsWithoutLabel() {
    $config = [
      'name' => 'someField',
      'type' => 'someType'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forField($config);
  }

  function testForFieldFailsWithoutType() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forField($config);
  }

  function testForFieldFailsWithKey() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'key' => 'someKey'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forField($config);
  }

  function testForFieldGetConfigFromFilter() {
    $config = 'ACFComposer/Fields/someField';
    $someField = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    Filters::expectApplied($config)
    ->once()
    ->andReturn($someField);
    $output = ResolveConfig::forField($config);
    $this->assertEquals($someField, $output);
  }
}
