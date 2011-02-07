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

/**
 * This class encapsulates entity information for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class Info {

  private $_class;
  private $_actor;
  private $_table;
  private $_id;
  private $_properties = Array();

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
    $this->_properties[] = $property;
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
    return $this->_properties;
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
