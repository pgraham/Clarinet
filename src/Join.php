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
namespace clarinet;

/**
 * This class encapsulates a Criteria join.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Join {

  /* The table to use on the left side of the join condition. */
  private $_lhsTbl;

  /* The column to use on the left side of the join condiction. */
  private $_lhsCol;

  /* The column to use on the right side of the join condition. */
  private $_rhsCol;

  /* The join being joined into the result set. */
  private $_table;

  /* The type of join. */
  private $_type;

  /**
   * Constructor.
   *
   * @param string $table
   * @param string $type
   */
  public function __construct($table, $type) {
    $this->_table = $table;
    $this->_type = $type;
  }

  /** 
   * Return the string representation of the join.
   *
   * @return string
   */
  public function __toString() {
    $escTable = Criteria::escapeFieldName($this->_table);
    $escLhsTbl = Criteria::escapeFieldName($this->_lhsTbl);
    $escLhsCol = Criteria::escapeFieldName($this->_lhsCol);
    $escRhsCol = Criteria::escapeFieldName($this->_rhsCol);

    $parts = array("$this->_type JOIN $escTable");
    if ($this->_rhsCol !== null) {
      $lhs = "$escLhsTbl.$escLhsCol";
      $rhs = "$escTable.$escRhsCol";
      $parts[] = "ON $lhs = $rhs";
    } else {
      $parts[] = "USING ($escLhsCol)";
    }

    return implode(' ', $parts);
  }

  /**
   * Getter for the table on the left side of the join condition
   *
   * @return string
   */
  public function getLhsTable() {
    return $this->_lhsTbl;
  }

  /**
   * Getter for the table being joined.
   *
   * @return string
   */
  public function getTable() {
    return $this->_table;
  }

  /**
   * Setter for the column on the left side of the join condition.
   *
   * @param string $lhsCol
   */
  public function setLhsColumn($lhsCol) {
    $this->_lhsCol = $lhsCol;
  }

  /**
   * Setter for the table on the left side of the join condition.
   *
   * @param string $lhsTbl
   */
  public function setLhsTable($lhsTbl) {
    $this->_lhsTbl = $lhsTbl;
  }

  /**
   * Setter for the column on the right side of the join condition.  The table
   * in the right side of the condition will always be the table being joined.
   *
   * @param string $rhsCol
   */
  public function setRhsColumn($rhsCol) {
    $this->_rhsCol = $rhsCol;
  }

}
