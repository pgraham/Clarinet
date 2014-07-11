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

use \zpt\anno\AnnotationFactory;
use \zpt\orm\model\parser\DefaultNamingStrategy;
use \zpt\orm\model\ModelCache;
use \zpt\orm\model\parser\ModelParser;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../../setup.php';

/**
 * This class tests the ModelParser class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class ParserTest extends TestCase {

  private $modelParser;
  private $modelCache;

  /**
   * Clear the cache of parsed model's before each test.
   */
  protected function setUp() {
    $annotationFactory = new AnnotationFactory();

    $namingStrategy = new DefaultNamingStrategy();
    $namingStrategy->setAnnotationFactory($annotationFactory);

    $this->modelParser = new ModelParser();
    $this->modelCache = new ModelCache();

    $this->modelCache->setModelParser($this->modelParser);

    $this->modelParser->setModelCache($this->modelCache);
    $this->modelParser->setAnnotationFactory($annotationFactory);
    $this->modelParser->setNamingStrategy($namingStrategy);

    $this->modelParser->init();

  }
  
  /**
   * Tests that the parse(...) method generates the expected array structure for
   * a mock\SimpleEntity model.
   */
  public function testParseSimpleEntity() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\SimpleEntity');
    $msg = print_r($info, true);

    $this->assertInstanceOf('zpt\orm\model\Model', $info, $msg);

    $this->assertEquals('zpt\orm\test\mock\SimpleEntity', $info->getClass(),
      $msg);
    $this->assertEquals('simple_entity', $info->getTable(), $msg);

    $properties = $info->getProperties();
    $this->assertInternalType('array', $properties, $msg);
    $name = null;
    $value = null;
    foreach ($properties AS $property) {
      $this->assertInstanceOf('zpt\orm\model\Property', $property, $msg);

      if ($property->getName() === 'name') {
        $name = $property;
      } else if ($property->getName() === 'value') {
        $value = $property;
      }
    }
    $this->assertNotNull($name);
    $this->assertNotNull($value);
    $this->assertEquals('name', $name->getColumn());
    $this->assertEquals('value', $value->getColumn());

    $id = $info->getId();;
    $this->assertInstanceOf('zpt\orm\model\Property', $id, $msg);
    $this->assertEquals('id', $id->getName(), $msg);
    $this->assertEquals('id', $id->getColumn(), $msg);
  }

  /**
   * Tests that the parse(...) method generates the expected objects for an
   * entity that declares a one-to-many relationship.
   */
  public function testParseOneToManyRelationship() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\OneToManyEntity');

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\OneToMany', $relationship);
  }

  /**
   * Tests that the parse(...) method generates the expected object for an
   * entity that declares a many-to-one relationship.
   */
  public function testParseManyToOneRelationship() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\ManyToOneEntity');

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\ManyToOne', $relationship);
  }

  /**
   * Tests that the parse(...) method gnerates the expected objects for a pair
   * of entities that declare both sides of a one-to-many relationship.
   */
  public function testParseOneToManyMirroredRelationship() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\OneToManyMirrorEntity');

    // The parsing process should have parsed and cached the mirror entity.
    $this->assertTrue(
      $this->modelCache->isCached('zpt\orm\test\mock\ManyToOneMirrorEntity'));

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\OneToMany', $relationship);

    $info = $this->modelParser->parse('zpt\orm\test\mock\ManyToOneMirrorEntity');

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\ManyToOne', $relationship);
  }

  /**
   * Tests that the parse(...) method generates the expected objects for an
   * entity that declares a many-to-many relationship.
   */
  public function testParseManyToManyRelationship() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\ManyToManyEntity');

    $this->assertTrue(
      $this->modelCache->isCached('zpt\orm\test\mock\SimpleEntity'));

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\ManyToMany', $relationship);
  }

  /**
   * Tests that the parse(...) method generates the expected objects for a pair
   * entities that declare both sides of a many-to-many relationship.
   */
  public function testMarseManyToManyTwoSidedRelationship() {
    $info = $this->modelParser->parse('zpt\orm\test\mock\ManyToManyMirrorLhsEntity');

    $this->assertTrue(
      $this->modelCache->isCached('zpt\orm\test\mock\ManyToManyMirrorRhsEntity'));

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\ManyToMany', $relationship);

    $info = $this->modelParser->parse('zpt\orm\test\mock\ManyToManyMirrorRhsEntity');

    $relationships = $info->getRelationships();
    $this->assertInternalType('array', $relationships);
    $this->assertEquals(1, count($relationships));

    $relationship = $relationships[0];
    $this->assertInstanceOf('zpt\orm\model\ManyToMany', $relationship);
  }
}
