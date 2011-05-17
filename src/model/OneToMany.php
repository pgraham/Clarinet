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
 * This class encapsulates a one-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class OneToMany extends AbstractRelationship {

  private $_rhsColumn;
  private $_rhsProperty;

  /**
   * Creates a new one-to-many relationship representation.
   *
   * @param Model $lhs The model on the left side of the relationship.
   * @param Model $rhs The model on the right side of the relationship.
   * @param string $lhsProperty The name of the property in the left hand side
   *   entity that contains the related right hand side instances.
   * @param string $rhsColumn The name of the column in the right side of the
   *   relationship that contains the id of the left side entity to which
   *   right side entities are related.
   * @param string $rhsProperty The name of the column in the right side of the
   *   relationship that contains the id of the left side entity to which
   *   right side entities are related.
   */
  public function __construct($lhs, $rhs, $lhsProperty, $rhsColumn,
    $rhsProperty)
  {
    parent::__construct($lhs, $rhs, $lhsProperty);
    $this->_rhsColumn = $rhsColumn;
    $this->_rhsProperty = $rhsProperty;
  }

  /**
   * Getter for the relationship's right side column.
   *
   * @return string The name of the column on the right side of the relationship
   */
  public function getRhsColumn() {
    return $this->_rhsColumn;
  }

  /**
   * Getter for the relationship's right side property.
   *
   * @return string The name of the property on the right side of the
   *   relationship
   */
  public function getRhsProperty() {
    return $this->_rhsProperty;
  }
}
