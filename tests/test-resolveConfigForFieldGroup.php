<?php

namespace ACFComposer\Tests;

require_once dirname(__DIR__) . '/lib/ACFComposer/ResolveConfig.php';

use Exception;
use Brain\Monkey\WP\Filters;
use ACFComposer\ResolveConfig;

class ResolveConfigForFieldGroupTest extends TestCase
{
    public function testForFieldGroupWithValidConfig()
    {
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

    public function testForFieldGroupFailsWithInvalidField()
    {
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

    public function testForFieldGroupFailsWithInvalidLocation()
    {
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

    public function testForFieldGroupWithNestedFilters()
    {
        $subFieldFilter = 'ACFComposer/Layout/subFieldLayout/SubFields';
        $subFieldConfig = [
            [
                'name' => 'nestedSubField',
                'label' => 'Nested Sub Field',
                'type' => 'text'
            ]
        ];
        $someLayoutFilter = 'ACFComposer/Layout/someLayout';
        $someLayoutConfig = [
            [
              'name' => 'SubField',
              'label' => 'Sub Field',
              'type' => 'text'
            ],
            $subFieldFilter
        ];
        $masterLayout = [
          'name' => 'masterLayout',
          'title' => 'Master Layout',
          'fields' => [
              $someLayoutFilter
          ],
          'location' => [
              [
                  [
                      'param' => 'someParam',
                      'operator' => 'someOperator',
                      'value' => 'someValue'
                  ]
              ]
          ]
        ];
        Filters::expectApplied($subFieldFilter)
            ->once()
            ->andReturn($subFieldConfig);
        Filters::expectApplied($someLayoutFilter)
            ->once()
            ->andReturn($someLayoutConfig);
        $output = ResolveConfig::forFieldGroup($masterLayout);
        $resolved_subfields = $output['fields'];
        $expectedResult = [
          [
            'name' => 'SubField',
            'label' => 'Sub Field',
            'type' => 'text',
            'key' => 'field_masterLayout_SubField'
          ],
          [
            'name' => 'nestedSubField',
            'label' => 'Nested Sub Field',
            'type' => 'text',
            'key' => 'field_masterLayout_nestedSubField'
          ]
        ];
        $this->assertEquals($expectedResult, $resolved_subfields);
    }
}
