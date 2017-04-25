<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

namespace ACFComposer\Tests;

/**
 * Construction plan test case.
 */
require_once dirname(__DIR__) . '/lib/ACFComposer/ACFComposer.php';

use Mockery;
use Brain\Monkey\Functions;
use ACFComposer\ACFComposer;

class ACFComposerTest extends TestCase
{
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testRegisterFieldGroup()
    {
        $config = 'this is a config';
        $fieldGroup = 'this is a field group';
        $returnValue = 'this is a return value';
        Mockery::mock('alias:ACFComposer\ResolveConfig')
            ->shouldReceive('forFieldGroup')
            ->with($config)
            ->once()
            ->andReturn($fieldGroup);
        Functions::expect('acf_add_local_field_group')
            ->with($fieldGroup)
            ->once()
            ->andReturn($returnValue);
        $output = ACFComposer::registerFieldGroup($config);
        $this->assertEquals($returnValue, $output);
    }
}
