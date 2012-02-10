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
 */
namespace clarinet\model;

use \ReflectionClass;
use \ReflectionException;

use \clarinet\Exception;
use \reed\reflection\Annotations;

/**
 * This class parses a model class' information into an array structure that is
 * expected by the generator classes.
 *
 * TODO Move static interface into a class in the base namespace that only
 *      provides a static interface.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Parser {

  /* Cache of completely parsed models */
  private static $_cache = Array();

  /**
   * Clears the cache of parsed models.  This is used for testing.
   *
   * @deprecated Remove once functionality has been migrated to a static class.
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
   * @deprecated Remove once functionality has been migrated to a static class.
   *
   * @param string $className The name of the model class to parse.
   * @return Model
   */
  public static function getModel($className) {
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
   * @deprecated Remove once functionality has been migrated to a static class.
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

  private $_model = null;

  /**
   * Instantiate a new model parser.  This is not recommended.  Instead use the
   * static getModel(...) method as this will make use of a cache to not
   * parse a model more than once.
   *
   * @param string $className The name of the model to parse.
   */
  public function __construct($className) {
    $this->_className = $className;

    try {
      $this->_class = new ReflectionClass($className);
    } catch (ReflectionException $e) {
      throw new Exception("Unable to reflect $className: {$e->getMessage()}",
        $e);
    }
    $docComment = $this->_class->getDocComment();
    $this->_classAnnotations = new Annotations($this->_class);

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
   * @return Model
   */
  public function parse() {
    if ($this->_model !== null) {
      return $this->_model;
    }

    $model = new Model($this->_className, $this->_classAnnotations);
    $model->setTable($this->_classAnnotations['entity']['table']);

    // Find the id column.  This is done before parsing other column types since
    // some default values rely on the id.
    $id = null;
    foreach ($this->_methods AS $method) {
      $annotations = new Annotations($method);
      $methodName = $method->getName();

      if (isset($annotations['id'])) {
        if ($id !== null) {
          $this->_fail("{$this->_className} has more than one id column"
            . " defined");
        }
        $id = $this->_parseId($model, $methodName, $annotations);
        $model->setId($id);

        // We continue to loop at this point to verify that only one id column
        // has be declared
      }
    }

    // Don't parse any further if no ID column has been defined
    if ($id === null) {
      $this->_fail("{$this->_className} does not define an Id column."
        . "  Use the @Id annotation to denote a column as the id column.");
    }

    foreach ($this->_methods AS $method) {
      $annotations = new Annotations($method);
      $methodName = $method->getName();

      if (isset($annotations['column'])) {
        $property = $this->_parseColumn($model, $methodName, $annotations);
        $model->addProperty($property);
      }
    }

    // Cache the model before parsing relationships in order to avoid inifinite
    // recursions when building a mirrored relationship
    self::$_cache[$this->_className] = $model;

    foreach ($this->_methods AS $method) {
      $annotations = new Annotations($method);
      $methodName = $method->getName();

      $rel = null;
      if (isset($annotations['onetomany'])) {
        $rel = $this->_parseOneToMany($methodName, $annotations);

      } else if (isset($annotations['manytoone'])) {
        $rel = $this->_parseManyToOne($methodName, $annotations);

      } else if (isset($annotations['manytomany'])) {
        $rel = $this->_parseManyToMany($methodName, $annotations, $model);
      }

      if ($rel !== null) {
        $model->addRelationship($rel);
      }
    }

    $numProperties    = count($model->getProperties());
    $numRelationships = count($model->getRelationships());
    if ($numProperties + $numRelationships == 0) {
      $this->_fail("{$this->_className} does not define any columns");
    }

    $this->_model = $model;
    return $this->_model;
  }

  /* Ensure that the model has a setter for the given property */
  private function _ensureSetter($propertyName) {
    if (!$this->_class->hasMethod("set$propertyName")) {
      $this->_fail("{$this->_className}: Entity getters must have a"
        . " matching setter");
    }
  }

  /*
   * Reset the instances cached Model object, clear the static cache and throw an 
   * exception with the given message.
   */
  private function _fail($msg) {
    $this->_model = null;
    self::clearCache();

    throw new Exception($msg);
  }

  /* Parse a method annotated with @Id */
  private function _parseId($model, $methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only a getter can be marked as"
        . " the id");
    }
    $propertyName = substr($methodName, 3);
    $this->_ensureSetter($propertyName);

    if (isset($annotations['column'])) {
      if (isset($annotations['enumerated'])) {
        // TODO - Raise a warning that the annotation will be ignored.
      }

      $property  = $this->_parseColumn($model, $methodName, $annotations);
    } else {
      // If no column annotation has been provided assume that the column is
      // named 'id'
      $property = new Property($model, $propertyName, 'id');
    }

    // The default type for ID columns is integer
    if (!isset($annotations['type'])) {
      $property->setType(Property::TYPE_INTEGER);
    }

    return $property;
  }

  /* Parse a method that is annotated with @Column */
  private function _parseColumn($model, $methodName, $annotations) {
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
    $property = new Property($model, $propertyName, $column);

    // Parse the type first so that the default value and enumerated values can
    // be cast to the appropriate type.
    if (isset($annotations['type'])) {
      $type = strtolower($annotations['type']);

      if (!in_array($type, Property::$ALL_TYPES)) {
        $this->_fail("{$this->_className}::$methodName: "
          . " Column type must be one of: "
          . implode(', ', Property::$ALL_TYPES) . ".");
      }
      $property->setType($type);
    }

    if (isset($annotations['enumerated'])) {
      if (!is_array($annotations['enumerated']['values'])) {
        $this->_fail("{$this->_className}::$methodName: "
          . " Enumerated annotation must contain a parameter 'values' that is"
          . " defined as an array.");
      }
      $property->setValues($annotations['enumerated']['values']);
    }

    if (isset($annotations['default'])) {
      $default = strtolower($annotations['default']);
      $property->setDefault($default);
    }

    return $property;
  }

  /* Parse a method that is annotated with @ManyToMany */
  private function _parseManyToMany($methodName, $annotations, $model) {
    if (substr($methodName, 0, 3) !== 'get') {
      $this->_fail("{$this->_className}: Only getters can be marked as"
        . " a many-to-many relationship");
    }
    $property = substr($methodName, 3);
    $this->_ensureSetter($property);

    // Make sure that a related entity is declared and get the model info for
    // that entity
    if (!isset($annotations['manytomany']['entity'])) {
      $this->_fail("{$this->_className}::$methodName: Many-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToMany(entity = <...>).");
    }

    $lhs = $model;
    $rhs = self::getModel($annotations['manytomany']['entity']);

    // Grab optional parameters or set defaults
    if (isset($annotations['manytomany']['table'])) {
      $linkTable = $annotations['manytomany']['table'];
    } else {
      $linkTable = $lhs->getTable() . '_' . $rhs->getTable() . '_link';
    }

    if (isset($annotations['manytomany']['localid'])) {
      $linkLhsId = $annotations['manytomany']['localid'];
    } else {
      $linkLhsId = $lhs->getTable() . '_' . $lhs->getId()->getColumn();
    }

    if (isset($annotations['manytomany']['foreignid'])) {
      $linkRhsId = $annotations['manytomany']['foreignid'];
    } else {
      $linkRhsId = $rhs->getTable() . '_' .$rhs->getId()->getColumn();
    }

    $rel = new ManyToMany($lhs, $rhs, $property, $linkTable, $linkLhsId,
      $linkRhsId);

    // Parse the order by property for retrieving related entities.
    if (isset($annotations['onetomany']['order'])) {
      $orderBy = $annotations['onetomany']['order'];
      $dir = 'ASC';
      if (isset($annotations['onetomany']['dir'])) {
        $dir = $annotations['onetomany']['dir'];
      }
      $rel->setOrderBy($orderBy, $dir);
    }

    return $rel;
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
      $this->_fail("{$this->_className}::$methodName: Many-to-one"
        . " relationships must declare the related entity. "
        . " E.g. @ManyToOne(entity = <...>).");
    }

    $lhs = self::getModel($this->_className);
    $rhs = self::getModel($annotations['manytoone']['entity']);

    if (isset($annotations['manytoone']['column'])) {
      $column = $annotations['manytoone']['column'];
    } else {
      $column = $rhs->getTable() . '_' . $rhs->getId()->getColumn();
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
      $this->_fail("{$this->_className}::$methodName: One-to-many"
        . " relationships must declare the related entity. "
        . " E.g. @OneToMany(entity = <...>).");
    }

    $lhs = self::getModel($this->_className);
    $rhs = self::getModel($annotations['onetomany']['entity']);

    // Parse the column on the right side that stores the id of the entity on
    // the left side
    if (isset($annotations['onetomany']['column'])) {
      $rhsColumn = $annotations['onetomany']['column'];
    } else {
      $rhsColumn = $lhs->getTable() . '_' . $lhs->getId()->getColumn();
    }

    $rel = new OneToMany($lhs, $rhs, $property, $rhsColumn);

    // Parse the order by property for retrieving related entities.
    if (isset($annotations['onetomany']['order'])) {
      $orderBy = $annotations['onetomany']['order'];
      $dir = 'ASC';
      if (isset($annotations['onetomany']['dir'])) {
        $dir = $annotations['onetomany']['dir'];
      }
      $rel->setOrderBy($orderBy, $dir);
    }

    // Parse whether or not to delete orphaned entities on the many side
    $deleteOrphans = false;
    if (isset($annotations['onetomany']['deleteorphans'])) {
      $deleteOrphans = (bool) $annotations['onetomany']['deleteorphans'];
    }
    $rel->deleteOrphans($deleteOrphans); 

    return $rel;
  }
}
