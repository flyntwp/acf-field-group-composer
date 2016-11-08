<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveConfigForFieldGroupTest extends TestCase {
  function testForFieldGroupWithValidConfig() {
    $fieldConfig = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $locationConfig = [
      'param' => 'someParam',
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $config = [
      'name' => 'someGroup',
      'title' => 'Some Group',
      'fields' => [$fieldConfig],
      'location' => [
        [$locationConfig]
      ]
    ];
    $output = ResolveConfig::forFieldGroup($config);
    $fieldConfig['key'] = 'field_someGroup_someField';
    $config['key'] = 'group_someGroup';
    $config['fields'] = [$fieldConfig];
    $this->assertEquals($config, $output);
  }

  function testForFieldGroupFailsWithInvalidField() {
    $fieldConfig = [
      'name' => 'someField',
      'label' => 'Some Field'
    ];
    $locationConfig = [
      'param' => 'someParam',
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $config = [
      'name' => 'someGroup',
      'title' => 'Some Group',
      'fields' => [$fieldConfig],
      'location' => [
        [$locationConfig]
      ]
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forFieldGroup($config);
  }

  function testForFieldGroupFailsWithInvalidLocation() {
    $fieldConfig = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $locationConfig = [
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $config = [
      'name' => 'someGroup',
      'title' => 'Some Group',
      'fields' => [$fieldConfig],
      'location' => [
        [$locationConfig]
      ]
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forFieldGroup($config);
  }
}
