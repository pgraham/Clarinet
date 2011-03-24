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

  /* Model for the entity on the left side of the relationship */
  protected $_lhs;

  /* Property in the left side entity that contains this relationship */
  protected $_lhsPropery;

  /* Model for the entity on the right side of the relationship */
  protected $_rhs;

  /**
   * Initiate a new relationship.
   *
   * @param Model $lhs The model entity on the left side of the
   *   relationship.
   * @param Model $rhs The model entity on the right side of the
   *   relationship.
   * @param string $lhsProperty The property  on the left side that contains the
   *   relationship
   */
  protected function __construct(Model $lhs, Model $rhs, $lhsProperty) {
    $this->_lhs = $lhs;
    $this->_rhs = $rhs;
    $this->_lhsProperty = $lhsProperty;
  }

  /**
   * Getter for the clarinet\model\Model object for the left side of the
   * relationship.
   *
   * @return Model
   */
  public function getLhs() {
    return $this->_lhs;
  }

  /**
   * Getter for the column on the left side that contains this relationship.
   * One the ManyToOne relationship returns a value for this method so the
   * default is to return null
   *
   * @return null
   */
  public function getLhsColumn() {
    return null;
  }

  /**
   * Getter for the property on the left side that contains this relationship.
   *
   * @return string Property name.
   */
  public function getLhsProperty() {
    return $this->_lhsProperty;
  }

  /**
   * Getter for the clarinet\model\Model object for the right side of the
   * relationship.
   *
   * @return Model
   */
  public function getRhs() {
    return $this->_rhs;
  }
}
