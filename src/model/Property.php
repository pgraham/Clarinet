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
 * This object encapsulates information about a model property.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Property {

  private $_name;
  private $_column;
  private $_values;

  /**
   * Create a new persisted property representation.
   *
   * @param string $name The name of the property.
   * @param string $column The name of the column in the database table in which
   *   instances are persisted.
   */
  public function __construct($name, $column) {
    $this->_name = $name;
    $this->_column = $column;
  }

  /**
   * Getter for the name of the property.
   *
   * @return string
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Getter for the name of the column in which the property's values are
   * stored.
   *
   * @return string
   */
  public function getColumn() {
    return $this->_column;
  }

  /**
   * Getter for the set of possible values for the property.
   *
   * @return array
   */
  public function getValues() {
    return $this->_values;
  }

  /**
   * Flag for whether or not the set of values for the property is restricted by
   * an enumeration.
   *
   * @return boolean
   */
  public function isEnumerated() {
    return $this->_values !== null;
  }

  /**
   * Setter for the set of accepted values for the property.
   *
   * @param array $values
   */
  public function setValues(Array $values) {
    $this->_values = $values;
  }
}
