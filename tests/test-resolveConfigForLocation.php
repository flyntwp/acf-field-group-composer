<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveConfigForLocationTest extends TestCase {
  function testForLocationWithValidConfig() {
    $config = [
      'param' => 'someParam',
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $output = ResolveConfig::forLocation($config);
    $this->assertEquals($config, $output);
  }

  function testForLocationFailsWithoutParam() {
    $config = [
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLocation($config);
  }

  function testForLocationFailsWithoutOperator() {
    $config = [
      'param' => 'someParam',
      'value' => 'someValue'
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLocation($config);
  }

  function testForLocationFailsWithoutValue() {
    $config = [
      'param' => 'someParam',
      'operator' => 'someOperator',
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forLocation($config);
  }
}
