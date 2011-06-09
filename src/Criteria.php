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
 * @package clarinet
 */
namespace clarinet;

/**
 * This class encapsulates a set of criteria for a SELECT statement.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class Criteria {

  /* Set of expressions that are AND'd together to create the criteria */
  private $_conditions = Array();

  /* Set of join clauses */
  private $_joins = Array();

  /* Limit for the number of rows to query. */
  private $_limit = null;

  /* List of columns to sort by with their direction TODO */
  private $_sorts = Array();

  /* Clauses are created with parameterized values, these are the parameters */
  private $_params = Array();

  /*
   * Set of select columns.  This will only be set to something other than null
   * if there are joins added to the criteria.
   */
  private $_selectColumns = null;

  /* The table to select from. */
  private $_table = null;

  /**
   * Return the SQL representation of the criteria.  This will be a complete SQL
   * statement.
   * 
   * @return string
   */
  public function __toString() {
    $clauses = Array();
    if ($this->_selectColumns === null) {
      $select = 'SELECT *';
    } else {
      $select = 'SELECT ' . implode(',', $this->_selectColumns);
    }
    $clauses[] = $select;

    $clauses[] = 'FROM ' . $this->_table;

    if (count($this->_joins) > 0) {
      $joins = str_replace('${table}', $this->_table, $this->_joins);
      $clauses[] = implode(' ', $joins);
    }

    if (count($this->_conditions) > 0) {
      $clauses[] = 'WHERE ' . implode(' AND ', $this->_conditions);
    }

    if (count($this->_sorts) > 0) {
      $clauses[] = 'ORDER BY ' . implode(',', $this->_sorts);
    }

    if ($this->_limit !== null) {
      $clauses[] = 'LIMIT ' .$this->_limit;
    }

    return implode(' ', $clauses);
  }

  /**
   * Add an equals (=) clause to the statement's WHERE condition.
   *
   * @param string $column The column to use as the left hand side of the
   *   expression.  If the criteria contains any joins then the column must be
   *   fully qualified.
   */
  public function addEquals($column, $value) {
    if (is_array($value)) {
      $this->addIn($column, $value);
      return;
    }

    $escCol = $this->_escapeFieldName($column);
    if ($value !== null) {
      $idx = count($this->_params);
      $paramName = ":param$idx";
      $this->_params[$paramName] = $value;

      $this->_conditions[] = "$escCol = $paramName";

    } else {
      $this->_conditions[] = "$escCol IS NULL";
    }
  }

  /**
   * Add an IN clause to the statement's WHERE condition.
   *
   * @param string $column The column to use as the left hand side of the
   *   expression.
   * @param array $values Array of values to check against
   */
  public function addIn($column, Array $values) {
    if (count($values) == 0) {
      return;
    }

    if (count($values) == 1) {
      $this->addEquals($column, $values[0]);
      return;
    }

    $escCol = $this->_escapeFieldName($column);
    $paramNames = Array();

    $idx = count($this->_params);
    foreach ($values AS $val) {
      $paramName = ":param$idx";
      $paramNames[] = $paramName;
      $this->_params[$paramName] = $val;
      $idx++;
    }
    $this->_conditions[] = "$escCol IN (" . implode(',', $paramNames) . ")";
  }

  /**
   * Add a JOIN clause to the criteria.  The name of the table gets substituted
   * at the time the criteria is transformed into an SQL string.
   *
   * @param string $table The name of the table to JOIN with.
   * @param string $lhs The name of the column in the left side of the JOIN to
   *   join on.
   * @param string $rhs The name of the column in the right side of the JOIN to
   *   join on. Optional. Default, null.  If null the USING syntax is used.
   */
  public function addJoin($table, $lhs, $rhs = null) {
    if ($rhs !== null) {
      $this->_joins[] = "JOIN $table ON \${table}.$lhs = $table.$rhs";
    } else {
      $this->_joins[] = "JOIN $table USING ($lhs)";
    }
  }

  /**
   * Add a WHERE condition using a LIKE operator.
   *
   * @param string $column The column to compare
   * @param mixed $value The value to compare against.  If an array then a
   *   tuple of OR conditions will be created, one for each value in the array.
   */
  public function addLike($column, $value) {
    $escCol = $this->_escapeFieldName($column);
    $idx = count($this->_params);
    
    if (is_array($value)) {
      $conditions = array();
      foreach ($value AS $val) {
        $paramName = ":param$idx";
        $this->_params[$paramName] = $val;
        $idx++;

        $conditions[] = "$escCol LIKE $paramName";
      }
      $this->_conditions[] = '(' . implode(' OR ', $conditions) . ')';

    } else {
      $paramName = ":param$idx";
      $this->_params[$paramName] = $value;
      $this->_conditions[] = "$escCol LIKE $paramName";
    }
  }

  /**
   * Add a select column.
   *
   * @param string $select The name of the column to select.
   */
  public function addSelect($select) {
    if ($this->_selectColumns === null) {
      $this->_selectColumns = Array();
    }
    $this->_selectColumns[] = $select;
  }

  /**
   * Add a sort column, or list of sort columns.
   *
   * @param mixed $sorts The sorts to add
   */
  public function addSort($sort) {
    if (!is_array($sort)) {
      $sort = explode(',', $sort);
    }

    foreach ($sort AS $col) {
      $this->_sorts[] = $this->_escapeFieldName(trim($col));
    }
  }

  /**
   * Getter for the values for any parameterized values in the criteria's
   * SQL representation.
   *
   * @return Array
   */
  public function getParameters() {
    return $this->_params;
  }

  /**
   * Getter for the name of the table that this criteria will query.
   *
   * @return string
   */
  public function getTable() {
    return $this->_table;
  }

  /**
   * Setter for the LIMIT clause of the SQL statement.
   *
   * @param integer $limit The maximum number of rows to return
   */
  public function setLimit($limit) {
    if (!is_int($limit)) {
      throw new Exception("Limit clause must be an integer value."
        . " $limit given");
    }
    $this->_limit = $limit;
  }

  /**
   * Setter for the table that this criteria will query.
   *
   * @param string $table
   */
  public function setTable($table) {
    $this->_table = $table;
  }

  /* Escape the given field name. */
  private function _escapeFieldName($fieldName) {
    return '`' . str_replace('`', '``', $fieldName) . '`';
  }
}
