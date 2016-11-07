<?php

require_once dirname(__DIR__) . '/lib/ACFComposer/Field.php';

use ACFComposer\TestCase;
use ACFComposer\Field;

class FieldTest extends TestCase {
  function testAssignsValidConfig() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $field = new Field($config);
    $this->assertEquals($config, $field->config);
  }

  function testFailsWithoutName() {
    $config = [
      'label' => 'Some Field',
      'type' => 'someType'
    ];
    $this->expectException(Exception::class);
    new Field($config);
  }

  function testFailsWithoutLabel() {
    $config = [
      'name' => 'someField',
      'type' => 'someType'
    ];
    $this->expectException(Exception::class);
    new Field($config);
  }

  function testFailsWithoutType() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field'
    ];
    $this->expectException(Exception::class);
    new Field($config);
  }

  function testFailsWithKey() {
    $config = [
      'name' => 'someField',
      'label' => 'Some Field',
      'type' => 'someType',
      'key' => 'someKey'
    ];
    $this->expectException(Exception::class);
    new Field($config);
  }
}
