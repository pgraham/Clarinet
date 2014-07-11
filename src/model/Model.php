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
namespace zpt\orm\model;

use \zpt\orm\model\Collection;
use \Exception;
use \InvalidArgumentException;

/**
 * This class encapsulates entity information for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Model implements Identifiable {

  /*
   * The name of the classes that will act upon this model.  Different actors
   * will be placed into different namespaces.
   */
  private $_actor;

  /*
   * Class level model annotations.
   */
  private $_annotations;

  /* The fully qualified name of the class that defines the model. */
  private $_class;

  /* The ID property of the model. */
  private $_id;

  /* The model's properties. */
  private $_properties = array();

  /* The model's relationships. */
  private $_relationships = array();

  /* THe model's associated collections. */
  private $_collections = array();

  /* The name of the table where model entities are persisted. */
  private $_table;

  /**
   * Create a new model representation for the model with the given class name.
   *
   * @param string $className The name of the class where the model is defined.
   */
  public function __construct($className, $annotations) {
    $this->_class = $className;
    $this->_actor = str_replace('\\', '_', $className);
    $this->_annotations = $annotations;
  }

  /**
   * Getter for generic annotations on the model class.  This allows
   * annotations to be retrieved as if they were bean properties of the
   * class, i.e., $model->getGatekeeper() would check if an annotation named
   * 'gatekeeper' is set on the model and if so return the value.
   *
   * Only methods of the form getXXX() are supported.
   *
   * @param string $name
   * @param array $args
   */
  public function __call($name, $args) {
    if (substr($name, 0, 3) !== 'get') {
      throw new Exception("Unsupported method invocation ($name) on a " .
        __CLASS__);
    }

    $prop = strtolower(substr($name, 3));
    if (!$this->_annotations->hasAnnotation($prop)) {
      return null;
    }
    return $this->_annotations[$prop];
  }

  /**
   * Add a collection property to the list.
   *
   * @param Collection $collection Persisted collection.
   */
  public function addCollection(Collection $collection) {
    $this->_collections[$collection->getName()] = $collection;
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
    $this->_relationships[$relationship->getName()] = $relationship;
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
   * Getter for the model's collection mappings.
   *
   * @return array
   */
  public function getCollections() {
    return $this->_collections;
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
   * Getter for the Identifiable instance's unique string identifier.
   *
   * @return string
   */
  public function getIdentifier() {
    return $this->_actor;
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
  public function getProperty($propId) {
    if (isset($this->_properties[$propId])) {
      return $this->_properties[$propId];
    }
    return null;
  }

  /**
   * Getter for the model's relationship with the given type and right side
   * model class.
   *
   * @param string $relId The identifier of the relationship to fetch.
   * ---
   * @param string $type The type of relationship to fetch.
   * @param string $model The model on the right side of the relationship.
   *
   * @return Relationship The requested relationship representation or null if
   *   it does not exist.
   */
  public function getRelationship() {
    if (func_num_args() == 1) {
      return $this->_getRelationshipById(func_get_arg(0));
    } else if (func_num_args() == 2) {
      return $this->_getRelationshipByType(func_get_arg(0), func_get_arg(1));
    }

    throw new InvalidArgumentException('getRelationship accepts either one'
      . ' argument as the relationship ID or two arguments as the type of'
      . ' relationship and class of related model respectively.');
  }

  /**
   * Getter for the model's entity relationships.
   *
   * @return Relationship[]
   */
  public function getRelationships() {
    return array_values($this->_relationships);
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
   * @param string $propId
   * @return boolean
   */
  public function hasProperty($propId) {
    return $this->getProperty($propId) !== null;
  }

  /**
   * Determines if the model has a relationship of the give type to the
   * specified model class.
   *
   * @param string $relId The id of the relationship to check for.
   * ---
   * @param string $type The type of relationship to check for.
   * @param string $model The type of model to check for.
   *
   * @return boolean
   */
  public function hasRelationship($type, $model) {
    if (func_num_args() === 1) {
      return $this->getRelationship(func_get_arg(0)) !== null;
    } else {
      return $this->getRelationship(func_get_arg(0), func_get_arg(1)) !== null;
    }
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

  /* Get a relationship by id. */
  private function _getRelationshipById($relId) {
    if (isset($this->_relationships[$relId])) {
      return $this->_relationships[$relId];
    }
    return null;
  }

  /* Get a relationship by type. */
  private function _getRelationshipByType($type, $model) {
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
}
