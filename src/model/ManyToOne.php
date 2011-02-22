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
 * This class encapsulates a many-to-one relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class ManyToOne extends AbstractRelationship {

  private $_column;

  /**
   * Creates a new Many-to-one relationship representation.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the model's property that contains the
   *   relationship.
   * @param string $column The name of the model table's column that contains
   *   the id of the related entity.
   */
  public function __construct($lhs, $rhs, $property, $column) {
    parent::__construct($lhs, $rhs, $property);
    $this->_column = $column;
  }

  /**
   * Returns the name of the column that represents the relationship in the
   * database
   */
  public function getLhsColumn() {
    return $this->_column;
  }
}
