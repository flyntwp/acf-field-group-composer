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
    $config['key'] = 'field_someField';
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
    $someField['key'] = "field_someField";
    $this->assertEquals($someField, $output);
  }

  function testForFieldWithValidSubField() {
    $subFieldConfig = [
      'name' => 'subField',
      'label' => 'Sub Field',
      'type' => 'someType'
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'sub_fields' => [$subFieldConfig]
    ];
    $output = ResolveConfig::forField($config);
    $subFieldConfig['key'] = 'field_someField_subField';
    $this->assertEquals($subFieldConfig, $output['sub_fields'][0]);
  }

  function testForFieldFailWithInvalidSubField() {
    $subFieldConfig = [
      'name' => 'subField',
      'label' => 'Sub Field'
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'sub_fields' => [$subFieldConfig]
    ];
    $this->expectException(Exception::class);
    ResolveConfig::forField($config);
  }

  function testForFieldWithValidLayout() {
    $layoutConfig = [
      'name' => 'someLayout',
      'label' => 'Some Layout'
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'layouts' => [$layoutConfig]
    ];
    $output = ResolveConfig::forField($config);
    $layoutConfig['key'] = 'field_someField_someLayout';
    $this->assertEquals($layoutConfig, $output['layouts'][0]);
  }

  function testAppliesResolveFilterGeneric() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $resolvedConfig = $config;
    $resolvedConfig['key'] = 'field_someField';
    Filters::expectApplied('ACFComposer/resolveEntity')
    ->once()
    ->with($resolvedConfig)
    ->andReturn(array_merge($resolvedConfig, ['foo' => 'bar']));

    $output = ResolveConfig::forField($config);
    $resolvedConfig['foo'] = 'bar';
    $this->assertEquals($resolvedConfig, $output);
  }

  function testAppliesResolveFilterByName() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $resolvedConfig = $config;
    $resolvedConfig['key'] = 'field_someField';
    Filters::expectApplied('ACFComposer/resolveEntity?name=someField')
    ->once()
    ->with($resolvedConfig)
    ->andReturn(array_merge($resolvedConfig, ['foo' => 'bar']));

    $output = ResolveConfig::forField($config);
    $resolvedConfig['foo'] = 'bar';
    $this->assertEquals($resolvedConfig, $output);
  }

  function testAppliesResolveFilterByKey() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $resolvedConfig = $config;
    $resolvedConfig['key'] = 'field_someField';
    Filters::expectApplied('ACFComposer/resolveEntity?key=field_someField')
    ->once()
    ->with($resolvedConfig)
    ->andReturn(array_merge($resolvedConfig, ['foo' => 'bar']));

    $output = ResolveConfig::forField($config);
    $resolvedConfig['foo'] = 'bar';
    $this->assertEquals($resolvedConfig, $output);
  }

  function testResolveConditionalLogicOnSameLevel() {
    $subFieldOne = [
      'name' => 'subField1',
      'label' => 'Sub Field 1',
      'type' => 'someType',
    ];
    $subFieldWithConditional = [
      'name' => 'subField2',
      'label' => 'Sub Field 2',
      'type' => 'someType',
      'conditional_logic' => [
        [
          [
            'fieldPath' => 'subField1',
            'operator' => 'someOp',
            'value' => 'someValue'
          ]
        ]
      ]
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'sub_fields' => [
        $subFieldOne,
        $subFieldWithConditional
      ]
    ];

    $output = ResolveConfig::forField($config);

    $config['key'] = 'field_someField';
    $subFieldOne['key'] = 'field_someField_subField1';
    $subFieldWithConditional['key'] = 'field_someField_subField2';
    $subFieldWithConditional['conditional_logic'][0][0]['field'] = 'field_someField_subField1';
    unset($subFieldWithConditional['conditional_logic'][0][0]['fieldPath']);
    $config['sub_fields'] = [
      $subFieldOne,
      $subFieldWithConditional
    ];
    $this->assertEquals($config, $output);
  }

  function testResolveConditionalLogicOnParentLevel() {
    $subFieldOne = [
      'name' => 'subField1',
      'label' => 'Sub Field 1',
      'type' => 'someType',
    ];
    $subFieldWithConditional = [
      'name' => 'subField2',
      'label' => 'Sub Field 2',
      'type' => 'someType',
      'conditional_logic' => [
        [
          [
            'fieldPath' => '../subField1',
            'operator' => 'someOp',
            'value' => 'someValue'
          ]
        ]
      ]
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'sub_fields' => [
        $subFieldOne,
        $subFieldWithConditional
      ]
    ];

    $output = ResolveConfig::forField($config);

    $config['key'] = 'field_someField';
    $subFieldOne['key'] = 'field_someField_subField1';
    $subFieldWithConditional['key'] = 'field_someField_subField2';
    $subFieldWithConditional['conditional_logic'][0][0]['field'] = 'field_subField1';
    unset($subFieldWithConditional['conditional_logic'][0][0]['fieldPath']);
    $config['sub_fields'] = [
      $subFieldOne,
      $subFieldWithConditional
    ];
    $this->assertEquals($config, $output);
  }

  function testResolveConditionalLogicOnParentsParentLevel() {
    $subFieldWithConditional = [
      'name' => 'subField2',
      'label' => 'Sub Field 2',
      'type' => 'someType',
      'conditional_logic' => [
        [
          [
            'fieldPath' => '../../subField1',
            'operator' => 'someOp',
            'value' => 'someValue'
          ]
        ]
      ]
    ];
    $subFieldOne = [
      'name' => 'subField1',
      'label' => 'Sub Field 1',
      'type' => 'someType',
      'sub_fields' => [$subFieldWithConditional]
    ];
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'sub_fields' => [$subFieldOne]
    ];

    $output = ResolveConfig::forField($config);

    $config['key'] = 'field_someField';
    $subFieldOne['key'] = 'field_someField_subField1';
    $subFieldWithConditional['key'] = 'field_someField_subField1_subField2';
    $subFieldWithConditional['conditional_logic'][0][0]['field'] = 'field_subField1';
    unset($subFieldWithConditional['conditional_logic'][0][0]['fieldPath']);
    $subFieldOne['sub_fields'] = [$subFieldWithConditional];
    $config['sub_fields'] = [$subFieldOne];
    $this->assertEquals($config, $output);
  }
}
