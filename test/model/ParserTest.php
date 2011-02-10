<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package clarinet/test
 */
namespace clarinet\test\model;

use \clarinet\model\Parser;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests the ModelParser class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class ParserTest extends TestCase {
  
  /**
   * Tests that the parse(...) method generates the expected array structure for
   * a mock\SimpleEntity model.
   */
  public function testParseSimpleEntity() {
    $parser = new Parser('clarinet\test\mock\SimpleEntity');
    $info = $parser->parse();
    $msg = print_r($info, true);

    $this->assertInstanceOf('clarinet\model\Info', $info, $msg);

    $this->assertEquals('clarinet\test\mock\SimpleEntity', $info->getClass(),
      $msg);
    $this->assertEquals('config_values', $info->getTable(), $msg);

    $properties = $info->getProperties();
    $this->assertInternalType('array', $properties, $msg);
    $name = null;
    $value = null;
    foreach ($properties AS $property) {
      $this->assertInstanceOf('clarinet\model\Property', $property, $msg);

      if ($property->getName() == 'Name') {
        $name = $property;
      } else if ($property->getName() == 'Value') {
        $value = $property;
      }
    }
    $this->assertNotNull($name, $msg);
    $this->assertNotNull($value, $msg);
    $this->assertEquals('name', $name->getColumn(), $msg);
    $this->assertEquals('value', $value->getColumn(), $msg);

    $id = $info->getId();;
    $this->assertInstanceOf('clarinet\model\Property', $id, $msg);
    $this->assertEquals('Id', $id->getName(), $msg);
    $this->assertEquals('id', $id->getColumn(), $msg);
  }

  /**
   * Tests that the parse(...) method generates the expected objects for an
   * entity that declares a one-to-many relationship.
   */
  public function testParseOneToManyRelationship() {
    $parser = new Parser('clarinet\test\mock\OneToManyEntity');
    $info = $parser->parse();
  }

  /**
   * Tests that the parse(...) method generates the expected object for an
   * entity that declares a many-to-one relationship.
   */
  public function testParseManyToOneRelationship() {
    $parser = new Parser('clarinet\test\mock\ManyToOneEntity');
    $info = $parser->parse();
  }

  /**
   * Tests that the parse(...) method gnerates the expected objects for a pair
   * of entities that declare both sides of a one-to-many relationship.
   */
  public function testParseOnToManyTwoSidedRelationship() {
    $parser = new Parser('clarinet\test\mock\OneToManyMirrorEntity');
    $info = $parser->parse();
  }

  /**
   * Tests that the parse(...) method generates the expected objects for an
   * entity that declares a many-to-many relationship.
   */
  public function testParseManyToManyRelationship() {
    $parser = new Parser('clarinet\test\mock\ManyToManyEntity');
    $info = $parser->parse();
  }

  /**
   * Tests that the parse(...) method generates the expected objects for a pair
   * entities that declare both sides of a many-to-many relationship.
   */
  public function testMarseManyToManyTwoSidedRelationship() {
    $parser = new Parser('clarinet\test\mock\ManyToManyMirrorLhsEntity');
    $info = $parser->parse();
  }
}
