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

/**
 * This class encapsulates entity information for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Model {

  private $_class;
  private $_actor;
  private $_table;
  private $_id;
  private $_properties = Array();
  private $_relationships = Array();

  /**
   * Create a new model Info object for the model with the given class name.
   *
   * @param string $className The name of the class represented by the instance.
   */
  public function __construct($className) {
    $this->_class = $className;
    $this->_actor = str_replace('\\', '_', $className);
  }

  /**
   * Add a Property object to the list of the model's persisted properties.
   *
   * @param Property $property Persisted property.
   */
  public function addProperty(Property $property) {
    $this->_properties[$property->getName()] = $property;
  }

  /**
   * Add a relationship object to the list of the model's persister properties.
   *
   * @param Relationship $relationship Entity relationship.
   */
  public function addRelationship(Relationship $relationship) {
    $this->_relationships[] = $relationship;
  }

  /**
   * Getter for the base class name for all actors for the model.  Actor's for
   * different functions will be placed into different namespaces.  This will
   * always be the fully qualified name of the model class with backslashes (\)
   * replaced with underscores (_).
   *
   * @return string Base class name for all of the represented model's actors.
   */
  public function getActor() {
    return $this->_actor;
  }

  /**
   * Getter for the name of the model class represented by the instance.
   *
   * @return string The name of the model class represented by the instance.
   */
  public function getClass() {
    return $this->_class;
  }

  /**
   * Getter for the Property object that represents the model's id property.
   *
   * @return Property
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * Getter for the model's persisted properties.  The list does NOT include the
   * id property.
   *
   * @return Property[]
   */
  public function getProperties() {
    return array_values($this->_properties);
  }

  /**
   * Getter for the property object representing the property with the given
   * name.
   *
   * @return Property
   */
  public function getProperty($name) {
    if (isset($this->_properties[$name])) {
      return $this->_properties[$name];
    }
    return null;
  }

  /**
   * Getter for the model's relationship with the given type and right side
   * model class.
   *
   * @param string $type The type of relationship to fetch.
   * @param string $model The model on the right side of the relationship.
   * @return Relationship The requested relationship representation or null if
   *   it does not exist.
   */
  public function getRelationship($type, $model) {
    $className = null;
    switch ($type) {
      case 'many-to-one':
      $className = 'clarinet\model\ManyToOne';
      break;

      case 'one-to-many':
      $className = 'clarinet\model\OneToMany';
      break;

      case 'many-to-many':
      $className = 'clarinet\model\ManyToMany';
      break;

      default:
      throw new Exception("Unrecognized relationship type : $type");
    }

    foreach ($this->_relationships AS $relationship) {
      $isType = get_class($relationship) == $className;
      $isModel = $relationship->getRhs()->getClass() == $model;

      if ($isType && $isModel) {
        return $relationship;
      }
    }
    return null;
  }

  /**
   * Getter for the model's entity relationships.
   *
   * @return Relationship[]
   */
  public function getRelationships() {
    return $this->_relationships;
  }

  /** 
   * Getter for the name of the table in which instances of the model are
   * persisted.
   *
   * @return string
   */
  public function getTable() {
    return $this->_table;
  }

  /**
   * Determines if the model has a property with the given name.
   *
   * @param string $name
   * @return boolean
   */
  public function hasProperty($name) {
    return $this->getProperty($name) !== null;
  }

  /**
   * Determines if the model has a relationship of the give type to the
   * specified model class.
   *
   * @param string $type The type of relationship to check for
   * @param string $model The type of model to check for
   * @return boolean
   */
  public function hasRelationship($type, $model) {
    return $this->getRelationship($type, $model) !== null;
  }

  /**
   * Setter for the Property object that represents the model's id property.
   *
   * @param Property $id
   */
  public function setId($id) {
    $this->_id = $id;
  }

  /**
   * Setter for the table in which instances of the model are persisted.
   *
   * @param string $table The name of the table in which instances of the model
   *   are persisted.
   */
  public function setTable($table) {
    $this->_table = $table;
  }
}
