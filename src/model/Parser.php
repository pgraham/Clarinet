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
 * TODO - Relationships that are declared in both participating entities will
 *        result in an infinite loop
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class Parser {

  /* Cache of completely parsed models */
  private static $_cache = Array();

  /*
   * Intermediate array of model tables used to prevent infinite loops
   * while parsing a relationship declared on both sides.
   */
  private static $_tables = Array();

  /*
   * Intermediate array of model ids used to prevent infinite loops
   * while parsing a relationship declared on both sides.
   */
  private static $_ids = Array();

  /**
   * Get the database mapping information for the specified model class.
   *
   * @param string $className The name of the model class to parse.
   * @return Info
   */
  public static function getModelInfo($className) {
    if (!isset(self::$_cache[$className])) {
      self::$_cache[$className] = new Parser($className);
    }

    return self::$_cache[$className]->parse();
  }

  /*
   * Used internally to get the name of an entity's table.  This is used to
   * prevent infinite loops when parsing a relationship that is declared on both
   * sides.
   */
  private static function _getTable($className) {
    if (!isset(self::$_tables[$className])) {
      // The parsing process with populate the table.  This is what breaks the
      // infinite loop
      self::getModelInfo($className);
    }
    return self::$_tables[$className];
  }

  /*
   * Used internally to get the name of an entity's id column.  This is used to
   * prevent infinite loops when parsing a relationship that is declared on both
   * sides.
   */
  private static function _getId($className) {
    if (!isset(self::$_ids[$className])) {
      // The parsing process with populate the id.  This is what breaks the
      // infinite loop
      self::getModelInfo($className);
    }
    return self::$_ids[$className];
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

  private $_modelInfo;

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

    $this->_modelInfo = new Info($this->_className);

    // Order in important here as related entities may need this information for
    // their own parsing
    $table = $this->_classAnnotations['entity']['table'];
    $this->_modelInfo->setTable($table);
    self::$_tables[$this->_className] = $table;

    // Find the id column.  This is done before parsing other column types since
    // some default values rely on the id.
    $id = null;
    foreach ($this->_methods AS $method) {
      $docComment = $method->getDocComment();
      $annotations = ReflectionHelper::getAnnotations($docComment);
      $methodName = $method->getName();

      if (isset($annotations['id'])) {
        if ($id !== null) {
          throw new Exception("{$this->_className} has more than one id column"
            . " defined");
        }
        $id = $this->_parseId($methodName, $annotations);
        $this->_modelInfo->setId($id);

        // We continue to loop at this point to verify that only one id column
        // has be declared
      }
    }

    if ($id === null) {
      throw new Exception("{$this->_className} does not define an Id column."
        . "  Use the @Id annotation to denote a column as the id column.");

      $this->_modelInfo = null;
    } else {
      self::$_ids[$this->_className] = $id;
    }

    // Parse any columns and/or relationships
    foreach ($this->_methods AS $method) {
      $docComment = $method->getDocComment();
      $annotations = ReflectionHelper::getAnnotations($docComment);
      $methodName = $method->getName();

      if (isset($annotations['column'])) {
        $property = $this->_parseColumn($methodName, $annotations);
        $this->_modelInfo->addProperty($property);

      } else if (isset($annotations['onetomany'])) {
        $relationship = $this->_parseOneToMany($methodName, $annotations);
        $this->_modelInfo->addRelationship($relationship);

      } else if (isset($annotations['manytoone'])) {
        $relationship = $this->_parseManyToOne($methodName, $annotations);
        $this->_modelInfo->addRelationship($relationship);

      } else if (isset($annotations['manytomany'])) {
        $relationship = $this->_parseManyToMany($methodName, $annotations);
        $this->_modelInfo->addRelationship($relationship);
      }
    }

    $numProperties    = count($this->_modelInfo->getProperties());
    $numRelationships = count($this->_modelInfo->getRelationships());
    if ($numProperties + $numRelationships == 0) {
      throw new Exception("{$this->_className} does not define any columns");

      $this->_modelInfo = null;
    }

    return $this->_modelInfo;
  }

  /* Ensure that the model has a setter for the given getter */
  private function _ensureSetter($propertyName) {
    if (!$this->_class->hasMethod("set$propertyName")) {
      throw new Exception("{$this->_className}: Entity getters must have a"
        . " matching setter");
    }
  }

  /* Parse a method annotated with @Id */
  private function _parseId($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only a getter can be marked as"
        . " the id");
    }
    $propertyName = substr($methodName, 3);
    $this->_ensureSetter($propertyName);

    if (isset($annotations['column'])) {
      return $this->_parseColumn($methodName, $annotations);
    } else {
      // If no column annotation has been provided assume that the column is
      // named 'id'
      return new Property($propertyName, 'id');
    }
  }

  /* Parse a method that is annotated with @Column */
  private function _parseColumn($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only getters can be marked as"
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
    return new Property($propertyName, $column);
  }

  /* Parse a method that is annotated with @ManyToMany */
  private function _parseManyToMany($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only getters can be marked as"
        . " a many-to-many relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    // Make sure that a related entity is declared and get the model info for
    // that entity
    if (!isset($annotations['manytomany']['entity'])) {
      throw new Exception("{$this->_className}->$methodName: Many-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToMany(entity = <...>).");
    }

    $rhs = $annotations['manytomany']['entity'];
    $rhsTable = self::_getTable($rhs);
    $rhsId    = self::_getId($rhs);
    $rhsIdColumn = $rhsId->getColumn();
    $rhsIdProperty = $rhsId->getName();

    // Grab optional parameters or set defaults
    if (isset($annotations['manytomany']['table'])) {
      $linkTable = $annotations['manytomany']['table'];
    } else {
      $linkTable = $this->_modelInfo->getTable() . '_' . $rhsTable . '_link';
    }

    if (isset($annotations['manytomany']['localid'])) {
      $linkLhsId = $annotations['manytomany']['localid'];
    } else {
      $linkLhsId = $this->_modelInfo->getTable() . '_'
        . $this->_modelInfo->getId()->getColumn();
    }

    if (isset($annotations['manytomany']['foreignid'])) {
      $linkRhsId = $annotations['manytomany']['foreignid'];
    } else {
      $linkRhsId = $rhsTable . '_' .$rhsId->getColumn();
    }

    $linkEntity = null;
    if (isset($annotations['manytomany']['linkentity'])) {
      $linkEntity = $annotations['manytomany']['linkentity'];
    }

    return new ManyToMany($property, $rhs, $rhsIdColumn, $rhsIdProperty,
      $linkTable, $linkLhsId, $linkRhsId, $linkEntity);
  }

  /* Parse a method that is annotated with @ManyToOne */
  private function _parseManyToOne($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only getters can be marked as"
        . " a many-to-one relationship");
    }
    $propertyName = substr($methodName, 3);
    $this->_ensureSetter($propertyName);

    if (!isset($annotations['manytoone']['entity'])) {
      throw new Exception("{$this->_className}->$methodName: Many-to-one"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToOne(entity = <...>).");
    }
    $entity = $annotations['manytoone']['entity'];
    $rhsTable = self::_getTable($entity);
    $rhsId = self::_getId($entity);


    if (isset($annotations['manytoone']['column'])) {
      $column = $annotations['manytoone']['column'];
    } else {
      $column = $rhsTable . '_' . $rhsId->getColumn();
    }

    return new ManyToOne($this->_className, $entity, $propertyName, $column);
  }

  /* Parse a method that is annotated with @OneToMany */
  private function _parseOneToMany($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only getters can be marked as"
        . " a one-to-many relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    if (!isset($annotations['onetomany']['entity'])) {
      throw new Exception("{$this->_className}->$methodName: One-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @OneToMany(entity = <...>).");
    }
    $entity = $annotations['onetomany']['entity'];
    $rhsTable = self::_getTable($entity);
    $rhsId = self::_getId($entity);

    if (isset($annotations['onetomany']['column'])) {
      $column = $annotations['onetomany']['column'];
    } else {
      $column = $rhsTable . '_' . $rhsId->getColumn();
    }

    return new OneToMany($entity, $property, $column);
  }
}
