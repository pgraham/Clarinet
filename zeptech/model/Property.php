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

use \clarinet\Exception;

/**
 * This object encapsulates information about a model property.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Property implements Identifiable {

  const TYPE_BOOLEAN   = 'boolean';
  const TYPE_DATE      = 'date';
  const TYPE_DECIMAL   = 'decimal';
  const TYPE_EMAIL     = 'email';
  const TYPE_FLOAT     = 'float';
  const TYPE_INTEGER   = 'integer';
  const TYPE_STRING    = 'string';
  const TYPE_TEXT      = 'text';
  const TYPE_TIMESTAMP = 'timestamp';

  public static $ALL_TYPES = array(
    self::TYPE_BOOLEAN,
    self::TYPE_DATE,
    self::TYPE_DECIMAL,
    self::TYPE_EMAIL,
    self::TYPE_FLOAT,
    self::TYPE_INTEGER,
    self::TYPE_STRING,
    self::TYPE_TEXT,
    self::TYPE_TIMESTAMP
  );

  private $_column;
  private $_default;
  private $_model;
  private $_name;
  private $_notNull;
  private $_type;
  private $_values;

  /**
   * Create a new persisted property representation.
   *
   * @param Model $model The model to which this property belongs.
   * @param string $name The name of the property.
   * @param string $column The name of the column in the database table in which
   *   instances are persisted.
   */
  public function __construct(Model $model, $name, $column) {
    $this->_model = $model;
    $this->_name = $name;
    $this->_column = $column;

    // Default type is string
    $this->_type = self::TYPE_STRING;

    // Default is to allow null values
    $this->_notNull = false;
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
   * Getter for the default value for the property.  If no default is specified
   * then the default is null.
   *
   * @return mixed
   */
  public function getDefault() {
    return $this->_default;
  }

  /**
   * Getter for the identifiable instance's identifier.
   *
   * // TODO This will not be unique among all Indentifiable instances, only
   *         among Property instances belonging to the same model.  This is
   *         sufficient for now but may not be in the future.
   */
  public function getIdentifier() {
    return $this->_name;
  }

  /**
   * Getter for the model to which the property belongs.
   *
   * @return Model
   */
  public function getModel() {
    return $this->_model;
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
   * Getter for the type of the property.
   *
   * @return string One of this class's TYPE_ constants
   */
  public function getType() {
    return $this->_type;
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
   * Getter/setter for whether or not the property is allowed to contain null
   * values.
   *
   * @param boolean $notNull If specified, then the method acts as a setter.
   * @return boolean
   */
  public function notNull($notNull = null) {
    if (is_bool($notNull)) {
      $this->_notNull = $notNull;
    }
    return $this->_notNull;
  }

  /**
   * Setter for the default value of the property.
   *
   * @param string $default The default value for the property.
   */
  public function setDefault($default) {
    $this->_default = $default;
  }

  /**
   * Setter for the type of the property.
   *
   * @param string $type One of this class's TYPE_ constants
   */
  public function setType($type) {
    if (!in_array($type, self::$ALL_TYPES)) {
      throw new Exception("Unsupported property type: $type");
    }
    $this->_type = $type;
  }

  /**
   * Setter for the set of accepted values for the property.
   *
   * @param array $values
   */
  public function setValues(array $values) {
    $this->_values = $values;
  }
}
