<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/Layout.php';

use ACFComposer\TestCase;
use ACFComposer\Layout;
use Brain\Monkey\WP\Filters;

class LayoutTest extends TestCase {
  function testAssignsValidConfig() {
    $config = [
      'name' => 'someLayout',
      'label' => 'Some Layout'
    ];
    $layout = new Layout($config);
    $this->assertEquals($config, $layout->config);
  }

  function testFailsWithoutName() {
    $config = [
      'label' => 'Some Layout'
    ];
    $this->expectException(Exception::class);
    new Layout($config);
  }

  function testFailsWithoutLabel() {
    $config = [
      'name' => 'someLayout'
    ];
    $this->expectException(Exception::class);
    new Layout($config);
  }

  function testFailsWithKey() {
    $config = [
      'name' => 'someLayout',
      'label' => 'Some Layout',
      'key' => 'someKey'
    ];
    $this->expectException(Exception::class);
    new Layout($config);
  }

  function testGetConfigFromFilter() {
    $config = 'ACFComposer/Layouts/someLayout';
    $someLayout = [
      'name' => 'someLayout',
      'label' => 'Some Layout',
      'type' => 'someType'
    ];
    Filters::expectApplied($config)
    ->once()
    ->andReturn($someLayout);
    $layout = new Layout($config);
    $this->assertEquals($someLayout, $layout->config);
  }
}
