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
 * Base class for relationships.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
abstract class AbstractRelationship implements Relationship {

  /* Info for the entity on the left side of the relationship */
  protected $_lhs;

  /* Property in the left side entity that contains this relationship */
  protected $_lhsPropery;

  /* Info for the entity on the right side of the relationship */
  protected $_rhs;

  /**
   * Initiate a new relationship.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $lhsProperty The property  on the left side that contains the
   *   relationship
   */
  protected function __construct($lhs, $rhs, $lhsProperty) {
    $this->_lhs = Parser::getModelInfo($lhs);
    $this->_rhs = Parser::getModelInfo($rhs);
    $this->_lhsProperty = $lhsProperty;
  }

  /**
   * Base implementation so that Relationship implementations that don't have
   * any special delete code don't need to declare this method.
   */
  public function getDeleteCode() {
    return null;
  }

  /**
   * Base implementation so that Relationship implementations that don't have
   * a left side column don't need to declare this method.
   */
  public function getLhsColumnName() {
    return null;
  }

  /**
   * Getter for the property on the left side that contains this relationship.
   *
   * @return string Property name.
   */
  public function getLhsPropertyName() {
    return $this->_lhsProperty;
  }

  /**
   * Base implementation so that Relationship implementations that don't need to
   * send a parameter in INSERT and UPDATE statements don't need to declare this
   * method.  If a relationship returns a value for {#getLhsColumnName()} this
   * method will probably need to be implemented.
   */
  public function getPopulateParameterCode() {
    return null;
  }

  /**
   * Base implementation so that Relationship implementations that don't need
   * special delete code don't need to implement this method.
   */
  public function getSaveCode() {
    return null;
  }
}
