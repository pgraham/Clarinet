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
 * This class encapsulates a Many-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class ManyToMany extends AbstractRelationship {

  private $_linkTable;
  private $_linkLhsId;
  private $_linkRhsId;

  /**
   * Create a new Many-to-many relationship representation.  The left side of
   * the relationship is implied by the Info object to which the relationship
   * belongs.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the property that contains the related
   *   entity.
   * @param string $linkTable The name of the table that contains the mapping.
   * @param string $linkLhsId The name of the column in the mapping table that
   *   contains the id of the entity on the left side of the relationship.
   * @param string $linkRhsId The name of the column in the mapping table that
   *   contains the id of the entity on the right side of the relationship.
   */
  public function __construct($lhs, $rhs, $property, $linkTable, $linkLhsId,
    $linkRhsId)
  {
    parent::__construct($lhs, $rhs, $property);
    $this->_linkTable = $linkTable;
    $this->_linkLhsId = $linkLhsId;
    $this->_linkRhsId = $linkRhsId;
  }

  /**
   * Get the name of the column in the link table that contains the left side
   * entity id.
   *
   * @return string Link table left side id column.
   */
  public function getLinkLhsId() {
    return $this->_linkLhsId;
  }

  /**
   * Get the name of the column in the link table that contains the right side
   * entity id.
   *
   * @return string Link table right side id column.
   */
  public function getLinkRhsId() {
    return $this->_linkRhsId;
  }

  /**
   * Get the name of the relationship's link table.
   *
   * @return string Link table name.
   */
  public function getLinkTable() {
    return $this->_linkTable;
  }
}
