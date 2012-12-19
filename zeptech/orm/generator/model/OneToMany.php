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
namespace zeptech\orm\generator\model;

use \Exception;

/**
 * This class encapsulates a one-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class OneToMany extends AbstractRelationship {

  private $_rhsColumn;
  private $_rhsProperty;
  private $_orderBy;
  private $_deleteOrphans = false;
  private $_fetchPolicy = 'lazy';

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
   */
  public function __construct($lhs, $rhs, $lhsProperty, $rhsColumn) {
    parent::__construct($lhs, $rhs, $lhsProperty);
    $this->_rhsColumn = $rhsColumn;

    $this->_fetchPolicy = 'lazy';
  }

  /**
   * Boolean getter/setter for whether or not orphaned entities on the many side
   * of the relationship should be deleted.
   *
   * @param boolean $deleteOrphans If provided acts as a setter.
   * @return boolean
   */
  public function deleteOrphans($deleteOrphans = null) {
    if ($deleteOrphans !== null) {
      $this->_deleteOrphans = $deleteOrphans;
    }
    return $this->_deleteOrphans;
  }

  /**
   * Getter for the relationship's fetch policy.  Will be either 'lazy' or
   * 'eager'.  This can be specified as the 'fetch' parameter of the
   * relationship's declaring annotation.
   *
   * @return string Either 'lazy' or 'eager'
   */
  public function getFetchPolicy() {
    return $this->_fetchPolicy;
  }

  /**
   * Getter for the order by clause to use when retrieving related entities.
   *
   * @return array
   */
  public function getOrderBy() {
    return $this->_orderBy;
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
   * Setter for the relationship's fetch policy, can be either 'lazy' or
   * 'eager'.  This is currently only honoured by the transformer.  All
   * relationships are retrieved eagerly by the persister, but this will change
   * in the future when proxies are introduced.
   *
   * TODO This is not currently supported
   *
   * @param string $fetchPolicy Either 'lazy' or 'eager'
   */
  public function setFetchPolicy($fetchPolicy) {
    if (!in_array($fetchPolicy, array('lazy', 'eager'))) {
      throw new Exception("Invalid fetch policy: $fetchPolicy");
    }

    $this->_fetchPolicy = $fetchPolicy;
  }

  /**
   * Setter for the order by clause to use when retrieving the related entities.
   *
   * @param string $column
   * @param string $direction
   */
  public function setOrderBy($column, $direction) {
    $this->_orderBy = array(
      'col' => $column,
      'dir' => $direction
    );
  }
}