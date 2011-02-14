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
 * @package clarinet/model
 */
namespace clarinet\model;

use \ReflectionClass;
use \ReflectionException;

use \clarinet\Exception;
use \reed\util\ReflectionHelper;

/**
 * This class parses a model class' information into an array structure that is
 * expected by the generator classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class Parser {

  /* Cache of completely parsed models */
  private static $_cache = Array();

  /**
   * Clears the cache of parsed models.  This is used for testing.
   */
  public static function clearCache() {
    self::$_cache = Array();
  }

  /**
   * Get the database mapping information for the specified model class.
   *
   * It is important to note that the instances returned by this method may not
   * have been populated with relationship information.  This is due to the
   * mechanism used to prevent infinite loops when parsing a relationship that
   * is declared on both sides.
   *
   * Due to this fact, if an error is encountered parsing any model, the entire
   * cache will be wiped out.
   *
   * @param string $className The name of the model class to parse.
   * @return Info
   */
  public static function getModelInfo($className) {
    if (!self::isCached($className)) {
      $parser = new Parser($className);
      $parser->parse();
    }

    return self::$_cache[$className];
  }

  /**
   * Returns a boolean indicating whether or not the model info for the given
   * model has been loaded into the cache.  This is used for testing.
   *
   * @param string $className The name of the model class.
   * @return boolean
   */
  public static function isCached($className) {
    return isset(self::$_cache[$className]);
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_class;
  private $_classAnnotations;
  private $_className;
  private $_methods;

  private $_modelInfo = null;

  /**
   * Instantiate a new model parser.  This is not recommended.  Instead use the
   * static getModelInfo(...) method as this will make use of a cache to not
   * parse a model more than once.
   *
   * @param string $className The name of the model to parse.
   */
  public function __construct($className) {
    $this->_className = $className;

    try {
      $this->_class = new ReflectionClass($className);
    } catch (ReflectionException $e) {
      throw new Exception("Unable to reflect $className", $e);
    }
    $docComment = $this->_class->getDocComment();
    $this->_classAnnotations = ReflectionHelper::getAnnotations($docComment);

    if (!isset($this->_classAnnotations['entity'])) {
      throw new Exception("Clarinet can only generate classes for models"
        . " that define an @Entity(table = <tablename>) annotation in the"
        . " class level doc comment");
    }

    if (!isset($this->_classAnnotations['entity']['table'])) {
      throw new Exception("@Entity annotation must define a table name:"
        . " @Entity(table = <tablename>)");
    }

    $this->_methods = $this->_class->getMethods();
  }

  /**
   * Parse the methods of the model class in order to build their database
   * mapping.
   *
   * @return Info
   */
  public function parse() {
    if ($this->_modelInfo !== null) {
      return $this->_modelInfo;
    }

    $modelInfo = new Info($this->_className);

    // Order in important here as related entities may need this information for
    // their own parsing
    $table = $this->_classAnnotations['entity']['table'];
    $modelInfo->setTable($table);

    // Find the id column.  This is done before parsing other column types since
    // some default values rely on the id.
    $id = null;
    foreach ($this->_methods AS $method) {
      $docComment = $method->getDocComment();
      $annotations = ReflectionHelper::getAnnotations($docComment);
      $methodName = $method->getName();

      if (isset($annotations['id'])) {
        if ($id !== null) {
          $this->_fail("{$this->_className} has more than one id column"
            . " defined");
        }
        $id = $this->_parseId($methodName, $annotations);
        $modelInfo->setId($id);

        // We continue to loop at this point to verify that only one id column
        // has be declared
      }
    }

    if ($id === null) {
      $this->_fail("{$this->_className} does not define an Id column."
        . "  Use the @Id annotation to denote a column as the id column.");
    }

    // Parse any columns and/or relationships
    foreach ($this->_methods AS $method) {
      $docComment = $method->getDocComment();
      $annotations = ReflectionHelper::getAnnotations($docComment);
      $methodName = $method->getName();

      if (isset($annotations['column'])) {
        $property = $this->_parseColumn($methodName, $annotations);
        $modelInfo->addProperty($property);
      }
    }

    self::$_cache[$this->_className] = $modelInfo;

    foreach ($this->_methods AS $method) {
      $docComment = $method->getDocComment();
      $annotations = ReflectionHelper::getAnnotations($docComment);
      $methodName = $method->getName();

      if (isset($annotations['onetomany'])) {
        $relationship = $this->_parseOneToMany($methodName, $annotations);
        $modelInfo->addRelationship($relationship);

      } else if (isset($annotations['manytoone'])) {
        $relationship = $this->_parseManyToOne($methodName, $annotations);
        $modelInfo->addRelationship($relationship);

      } else if (isset($annotations['manytomany'])) {
        $relationship = $this->_parseManyToMany($methodName, $annotations,
          $modelInfo);
        $modelInfo->addRelationship($relationship);
      }
    }

    $numProperties    = count($modelInfo->getProperties());
    $numRelationships = count($modelInfo->getRelationships());
    if ($numProperties + $numRelationships == 0) {
      $this->_fail("{$this->_className} does not define any columns");
    }

    $this->_modelInfo = $modelInfo;
    return $this->_modelInfo;
  }

  /* Ensure that the model has a setter for the given getter */
  private function _ensureSetter($propertyName) {
    if (!$this->_class->hasMethod("set$propertyName")) {
      $this->_fail("{$this->_className}: Entity getters must have a"
        . " matching setter");
    }
  }

  /*
   * Reset the instances cached Info object, clear the static cache and throw an 
   * exception with the given message.
   */
  private function _fail($msg) {
    $this->_modelInfo = null;
    self::clearCache();

    throw new Exception($msg);
  }

  /* Parse a method annotated with @Id */
  private function _parseId($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only a getter can be marked as"
        . " the id");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    if (isset($annotations['column'])) {
      if (isset($annotations['enumerated'])) {
        // TODO - Raise a warning that the annotation will be ignored.
      }
      return $this->_parseColumn($methodName, $annotations);
    } else {
      // If no column annotation has been provided assume that the column is
      // named 'id'
      return new Property($property, 'id');
    }
  }

  /* Parse a method that is annotated with @Column */
  private function _parseColumn($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only getters can be marked as"
        . " columns");
    }
    $propertyName = substr($methodName, 3);
    $this->_ensureSetter($propertyName);

    if (isset($annotations['column']['name'])) {
      $column = $annotations['column']['name'];
    } else {
      // If no column name has been specified assume that the column is the
      // lowercased equivalent of the property name
      // TODO - Update this to expand camel casing to underscores
      $column = strtolower($propertyName);
    }
    $property = new Property($propertyName, $column);

    if (isset($annotations['enumerated'])) {
      $property->setValues($annotations['enumerated']['values']);
    }
    return $property;
  }

  /* Parse a method that is annotated with @ManyToMany */
  private function _parseManyToMany($methodName, $annotations, $modelInfo) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only getters can be marked as"
        . " a many-to-many relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    // Make sure that a related entity is declared and get the model info for
    // that entity
    if (!isset($annotations['manytomany']['entity'])) {
      $this->_fail("{$this->_className}->$methodName: Many-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToMany(entity = <...>).");
    }

    $lhs = $this->_className;
    $rhs = $annotations['manytomany']['entity'];

    $rhsInfo = self::getModelInfo($rhs);
    $rhsTable = $rhsInfo->getTable();
    $rhsId = $rhsInfo->getId();
    $rhsIdColumn = $rhsId->getColumn();
    $rhsIdProperty = $rhsId->getName();

    // Grab optional parameters or set defaults
    if (isset($annotations['manytomany']['table'])) {
      $linkTable = $annotations['manytomany']['table'];
    } else {
      $linkTable = $modelInfo->getTable() . '_' . $rhsTable . '_link';
    }

    if (isset($annotations['manytomany']['localid'])) {
      $linkLhsId = $annotations['manytomany']['localid'];
    } else {
      $linkLhsId = $modelInfo->getTable() . '_'
        . $modelInfo->getId()->getColumn();
    }

    if (isset($annotations['manytomany']['foreignid'])) {
      $linkRhsId = $annotations['manytomany']['foreignid'];
    } else {
      $linkRhsId = $rhsTable . '_' .$rhsId->getColumn();
    }

    return new ManyToMany($property, $rhs, $rhsIdColumn, $rhsIdProperty,
      $linkTable, $linkLhsId, $linkRhsId);
  }

  /* Parse a method that is annotated with @ManyToOne */
  private function _parseManyToOne($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only getters can be marked as"
        . " a many-to-one relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    if (!isset($annotations['manytoone']['entity'])) {
      $this->_fail("{$this->_className}->$methodName: Many-to-one"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToOne(entity = <...>).");
    }

    $lhs = $this->_className;
    $rhs = $annotations['manytoone']['entity'];

    $rhsInfo = self::getModelInfo($rhs);
    if (isset($annotations['manytoone']['column'])) {
      $column = $annotations['manytoone']['column'];
    } else {
      $column = $rhsInfo->getTable() . '_' . $rhsInfo->getId()->getColumn();
    }

    return new ManyToOne($lhs, $rhs, $property, $column);
  }

  /* Parse a method that is annotated with @OneToMany */
  private function _parseOneToMany($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only getters can be marked as"
        . " a one-to-many relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    if (!isset($annotations['onetomany']['entity'])) {
      $this->_fail("{$this->_className}->$methodName: One-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @OneToMany(entity = <...>).");
    }

    $lhs = $this->_className;
    $rhs = $annotations['onetomany']['entity'];

    $lhsInfo = self::getModelInfo($lhs);

    // Parse the column on the right side that stores the id of the entity on
    // the left side
    if (isset($annotations['onetomany']['column'])) {
      $rhsColumn = $annotations['onetomany']['column'];
    } else {
      $rhsColumn = $lhsInfo->getTable() . '_' . $lhsInfo->getId()->getColumn();
    }

    // Parse the property on the right side that stores the id of the entity on
    // the left side.
    // TODO If the no property is specified and the default property does not
    //      exist, search the right hand side model for a relationship that
    //      mirrors this relationship and use that property in the persister to
    //      maintain the persisted relationship
    if (isset($annotations['onetomany']['property'])) {
      $rhsProperty = $annotations['onetomany']['property'];
    } else {
      $lhsBaseName = array_pop(explode('\\', $lhsInfo->getClass()));
      $rhsProperty = $lhsBaseName . 'Id';
    }

    return new OneToMany($lhs, $rhs, $property, $rhsColumn, $rhsProperty);
  }
}
