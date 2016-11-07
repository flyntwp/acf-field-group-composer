<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */
require_once dirname(__DIR__) . '/lib/ACFComposer/ACFComposer.php';

use ACFComposer\TestCase;
use ACFComposer\ACFComposer;

class ACFComposerTest extends TestCase {
  function testSimple() {
    $this->assertTrue(true);
  }
}
