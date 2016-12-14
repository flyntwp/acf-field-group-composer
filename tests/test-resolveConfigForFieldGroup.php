<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use ACFComposer\TestCase;
use ACFComposer\ResolveConfig;
use Brain\Monkey\WP\Filters;

class ResolveConfigForFieldGroupTest extends TestCase {
  function testForFieldGroupWithValidConfig() {
    $filterName = 'ACFComposer/Fields/someField';
    $fieldConfig = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $fieldConfigMulti = [
      [
        'name' => 'someField1',
        'label' => 'Some Field1',
        'type' => 'someType'
      ],
      [
        'name' => 'someField2',
        'label' => 'Some Field2',
        'type' => 'someType'
      ]
    ];
    $locationConfig = [
      'param' => 'someParam',
      'operator' => 'someOperator',
      'value' => 'someValue'
    ];
    $config = [
      'name' => 'someGroup',
      'title' => 'Some Group',
      'fields' => [
        $filterName,
        $fieldConfig,
        $fieldConfigMulti
      ],
      'location' => [
        [$locationConfig]
      ]
    ];

    Filters::expectApplied($filterName)
    ->once()
    ->andReturn($fieldConfig);

    $output = ResolveConfig::forFieldGroup($config);
    $fieldConfig['key'] = 'field_someGroup_someField';
    $fieldConfigMulti[0]['key'] = 'field_someGroup_someField1';
    $fieldConfigMulti[1]['key'] = 'field_someGroup_someField2';
    $config['key'] = 'group_someGroup';
    $config['fields'] = [$fieldConfig, $fieldConfig, $fieldConfigMulti[0], $fieldConfigMulti[1]];
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
