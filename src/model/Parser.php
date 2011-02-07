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
 * @package clarinet
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
 * @package clarinet
 */
class Parser {

  private static $_cache = Array();

  public static function getModelInfo($className) {
    if (!isset(self::$_cache[$className])) {
      self::$_cache[$className] = new Parser($className);
    }

    return self::$_cache[$className]->parse();
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

  public function parse() {
    if ($this->_modelInfo !== null) {
      return $this->_modelInfo;
    }

    $this->_modelInfo = new Info($this->_className);
    $this->_modelInfo->setTable($this->_classAnnotations['entity']['table']);

    $properties = Array();
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

      } else if (isset($annotations['column'])) {
        $property = self::_parseColumn($methodName, $annotations);
        $this->_modelInfo->addProperty($property);
      }
    }

    if ($id === null) {
      throw new Exception("{$this->_className} does not define an Id column."
        . "  Use the @Id annotation to denote a column as the id column.");

      $this->_modelInfo = null;
    }

    if (count($this->_modelInfo->getProperties()) == 0) {
      throw new Exception("{$this->_className} does not define any columns");

      $this->_modelInfo = null;
    }

    return $this->_modelInfo;
  }

  /* Parse a method annotated with @Id */
  private function _parseId($methodName, $annotations) {
    if (substr($methodName, 0, 3) !== 'get') {
      throw new Exception("{$this->_className}: Only a getter can be marked as"
        . " the id");
    }

    $propertyName = substr($methodName, 3);
    if (!$this->_class->hasMethod("set$propertyName")) {
      throw new Exception("{$this->_className}: Id getter must have a matching"
        . " setter");
    }

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
    if (!$this->_class->hasMethod("set$propertyName")) {
      throw new Exception("{$this->_className}->$methodName: Column getters"
        . " must have a matching setter");
    }

    return new Property($propertyName, $annotations['column']['name']);
  }
}
