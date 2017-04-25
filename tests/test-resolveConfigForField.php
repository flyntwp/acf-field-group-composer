<?php

namespace ACFComposer\Tests;

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use Exception;
use Brain\Monkey\WP\Filters;
use ACFComposer\ResolveConfig;

class ResolveConfigForFieldTest extends TestCase
{
    public function testForFieldWithValidConfig()
    {
        $config = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType'
        ];
        $output = ResolveConfig::forField($config);
        $config['key'] = 'field_someField';
        $this->assertEquals($config, $output);
    }

    public function testForFieldFailsWithoutName()
    {
        $config = [
            'label' => 'Some Field',
            'type' => 'someType'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forField($config);
    }

    public function testForFieldFailsWithoutLabel()
    {
        $config = [
            'name' => 'someField',
            'type' => 'someType'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forField($config);
    }

    public function testForFieldFailsWithoutType()
    {
        $config = [
            'name' => 'someField',
            'label' => 'Some Field'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forField($config);
    }

    public function testForFieldFailsWithKey()
    {
        $config = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType',
            'key' => 'someKey'
        ];
        $this->expectException(Exception::class);
        ResolveConfig::forField($config);
    }

    public function testForFieldGetConfigFromFilter()
    {
        $config = 'ACFComposer/Fields/someField';
        $someField = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType'
        ];
        Filters::expectApplied($config)
            ->with(null)
            ->once()
            ->andReturn($someField);
        $output = ResolveConfig::forField($config);
        $someField['key'] = "field_someField";
        $this->assertEquals($someField, $output);
    }

    public function testForFieldGetConfigFromFilterWithArgument()
    {
        $config = 'ACFComposer/Fields/someField#prefix';
        $filter = 'ACFComposer/Fields/someField';
        $someField = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType'
        ];
        Filters::expectApplied($filter)
            ->with(null, 'prefix')
            ->once()
            ->andReturn($someField);
        $output = ResolveConfig::forField($config);
        $someField['name'] = "prefix_someField";
        $someField['key'] = "field_prefix_someField";
        $this->assertEquals($someField, $output);
    }

    public function testMultipleForFieldGetConfigFromFilterWithArgument()
    {
        $config = 'ACFComposer/Fields/someField#prefix';
        $filter = 'ACFComposer/Fields/someField';
        $someField = [
            [
                'name' => 'someField',
                'label' => 'Some Field',
                'type' => 'someType'
            ],
            [
                'name' => 'someOtherField',
                'label' => 'Some Field',
                'type' => 'someType'
            ]
        ];
        Filters::expectApplied($filter)
            ->with(null, 'prefix')
            ->once()
            ->andReturn($someField);
        $output = ResolveConfig::forField($config);
        $someField[0]['name'] = "prefix_someField";
        $someField[0]['key'] = "field_prefix_someField";
        $someField[1]['name'] = "prefix_someOtherField";
        $someField[1]['key'] = "field_prefix_someOtherField";
        $this->assertEquals($someField, $output);
    }

    public function testForFieldGetConfigFromFilterWithArgumentAndConditionalLogic()
    {
        $config = [
            'ACFComposer/Fields/someField#prefix',
            'ACFComposer/Fields/someFieldWithConditional#otherprefix'
        ];

        $filter = 'ACFComposer/Fields/someField';
        $someField = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType'
        ];
        Filters::expectApplied($filter)
            ->with(null, 'prefix')
            ->once()
            ->andReturn($someField);

        $filterConditional = 'ACFComposer/Fields/someFieldWithConditional';
        $someFieldWithConditional = [
            'name' => 'someOtherField',
            'label' => 'Some Other Field',
            'type' => 'someType',
            'conditional_logic' => [
                [
                    [
                        'fieldPath' => 'someField',
                        'operator' => '==',
                        'value' => 'someValue'
                    ]
                ]
            ]
        ];
        Filters::expectApplied($filterConditional)
            ->with(null, 'otherprefix')
            ->once()
            ->andReturn($someFieldWithConditional);

        $output = array_map(function ($singleConfig) {
            return ResolveConfig::forField($singleConfig);
        }, $config);

        $someField['key'] = 'field_prefix_someField';
        $someField['name'] = 'prefix_someField';

        $someFieldWithConditional['key'] = 'field_otherprefix_someOtherField';
        $someFieldWithConditional['name'] = 'otherprefix_someOtherField';
        $someFieldWithConditional['conditional_logic'][0][0]['field'] = 'field_otherprefix_someField';
        unset($someFieldWithConditional['conditional_logic'][0][0]['fieldPath']);
        $config = [
            $someField,
            $someFieldWithConditional
        ];
        $this->assertEquals($config, $output);
    }

    public function testForFieldTriggerErrorWithoutFilter()
    {
        $config = 'ACFComposer/Fields/someField';
        Filters::expectApplied($config)
            ->once()
            ->andReturn(null);
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $output = ResolveConfig::forField($config);
    }

    public function testForFieldReturnEmptyArrayWithoutFilter()
    {
        $config = 'ACFComposer/Fields/someField';
        Filters::expectApplied($config)
            ->once()
            ->andReturn(null);
        $output = @ResolveConfig::forField($config);
        $this->assertEquals($output, []);
    }

    public function testForFieldWithValidSubField()
    {
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

    public function testForFieldFailWithInvalidSubField()
    {
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

    public function testForFieldWithValidLayout()
    {
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

    public function testAppliesResolveFilterGeneric()
    {
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

    public function testAppliesResolveFilterByName()
    {
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

    public function testAppliesResolveFilterByKey()
    {
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

    public function testResolveConditionalLogicOnSameLevel()
    {
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

    public function testResolveConditionalLogicOnParentLevel()
    {
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

    public function testResolveConditionalLogicOnParentsParentLevel()
    {
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

    public function testResolveMultipleFieldsFromFilter()
    {
        $filter = 'ACFComposer/Fields/subField';
        $subFieldTwo = [
            'name' => 'subFieldTwo',
            'label' => 'Sub Field Two',
            'type' => 'someType',
        ];
        $config = [
            'name' => 'someField',
            'label' => 'Some Field',
            'type' => 'someType',
            'sub_fields' => [
                $filter,
                $subFieldTwo
            ]
        ];
        $filterFieldOne = [
            'name' => 'filterFieldOne',
            'label' => 'Filter Field One',
            'type' => 'someType',
        ];
        $filterFieldTwo = [
            'name' => 'filterFieldTwo',
            'label' => 'Filter Field Two',
            'type' => 'someType',
        ];
        Filters::expectApplied($filter)
            ->once()
            ->andReturn([
                $filterFieldOne,
                $filterFieldTwo
            ]);
        $output = ResolveConfig::forField($config);
        $subFieldTwo['key'] = 'field_someField_subFieldTwo';
        $filterFieldOne['key'] = 'field_someField_filterFieldOne';
        $filterFieldTwo['key'] = 'field_someField_filterFieldTwo';
        $config['key'] = 'field_someField';
        $config['sub_fields'] = [
            $filterFieldOne,
            $filterFieldTwo,
            $subFieldTwo
        ];
        $this->assertEquals($config, $output);
    }
}
