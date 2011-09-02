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

  // 
  // Constants representing all valid column transformations
  //

  const EXTRACT_DATE = 'EXTRACT_DATE';

  //
  // Constants representing all of the valid predicate operators
  //

  const OP_EQUALS = '=';
  const OP_NOT_EQUALS = '<>';
  const OP_GREATER_THAN = '>';
  const OP_GREATER_EQUALS = '>=';
  const OP_LESS_THAN = '<';
  const OP_LESS_EQUALS = '<=';
  const OP_LIKE = 'LIKE';

  //
  // Constants representing all of the valid sorts directions
  // 

  const SORT_ASC = 'asc';
  const SORT_DESC = 'desc';

  /*
   * Array of supported predicate values that by-pass parameterization.  The
   * values are defined as lambas so that they can be distinguished from a
   * literal value named the same as the function.
   */
  private static $_funcs;

  /* Whether or not the supported function values are initialized. */
  private static $_initialized = false;

  /*
   * Array of operators supported by the addPredicate() method.  This array only
   * contains operators that have a single right hand side value.  Other
   * operators (IN, BETWEEN, IS NULL and IS NOT NULL) have their own methods.
   */
  private static $_ops = array(
    self::OP_EQUALS,
    self::OP_NOT_EQUALS,
    self::OP_GREATER_THAN,
    self::OP_GREATER_EQUALS,
    self::OP_LESS_THAN,
    self::OP_LESS_EQUALS,
    self::OP_LIKE
  );

  /*
   * Array of support column transformation values that allow columns to be
   * modified as part of a predicate without being backticked (`).
   */
  private static $_transforms;

  /**
   * Getter for the function lambda that performs the requested transformation.
   * TODO Separate Column Transformation into its own class with a protected
   *      constructor.  This will allow parameterized transformations,
   *      e.g. getColumnTransformation("CAST_AS_TYPE", 'DECIMAL', 10, 2),  to be
   *      constructed that can also be vetted as being valid by the
   *      addPredicate(...) function.
   *
   * @param string $transformation
   * @return function
   */
  public static function getColumnTransformation($transformation) {
    self::init();

    $cmp = strtoupper($transformation);
    if (!array_key_exists($cmp, self::$_transforms)) {
      throw self::_newInvalidTransformationException($transformation);
    }

    return self::$_transforms[$cmp];
  }

  /**
   * Getter for the function lambda for the predicate function value with the
   * given name.
   *
   * @param string $func
   */
  public static function getPredicateFunction($func) {
    self::init();

    $name = strtoupper($func);
    if (!array_key_exists($name, self::$_funcs)) {
      throw new Exception("Unrecognized predicate function: $func");
    }

    return self::$_funcs[$name];
  }

  /**
   * Static initializer.
   */
  protected static function init() {
    if (self::$_initialized) {
      return;
    }
    self::$_initialized = true;

    self::$_funcs = array();

    self::$_funcs['CURRENT_DATE'] = function () {
      return 'CURRENT_DATE';
    };

    self::$_funcs['CURRENT_TIME'] = function () {
      return 'CURRENT_TIME';
    };

    self::$_funcs['CURRENT_TIMESTAMP'] = function () {
      return 'CURRENT_TIMESTAMP';
    };

    self::$_transforms = array();

    self::$_transforms['EXTRACT_DATE'] = function ($escCol) {
      return "DATE($escCol)";
    };
  }

  /* Escape the given field name. */
  private static function _escapeFieldName($fieldName) {
    if (strpos($fieldName, '.') === false) {
      return '`' . str_replace('`', '``', $fieldName) . '`';
    }

    $parts = explode('.', $fieldName);
    $escaped = array();
    foreach ($parts AS $part) {
      $escaped[] = '`' . str_replace('`', '``', $part) . '`';
    }
    return implode('.', $escaped);
  }

  /* Build and throw an exception for the given invalid cast type. */
  private static function _newInvalidTransformationException($transform) {
    return new Exception("Invalid transformation: $transform");
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  /* Set of expressions that are AND'd together to create the criteria */
  private $_conditions = Array();

  /* The query's distinct clause */
  private $_distinct = false;

  /* Set of join clauses */
  private $_joins = Array();

  /* Limit for the number of rows to query. */
  private $_limit = null;

  /* Offset from the beginning of the result from which to select rows. */
  private $_offset = null;

  /* Clauses are created with parameterized values, these are the parameters */
  private $_params = Array();

  /*
   * Set of select columns.  This will only be set to something other than null
   * if there are joins added to the criteria.
   */
  private $_selectColumns = null;

  /* List of columns to sort by with their direction */
  private $_sorts = array();

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

    $select = 'SELECT ';
    if ($this->_distinct === true) {
      $select .= 'DISTINCT ';
    }
    if ($this->_selectColumns === null) {
      $select .= "$this->_table.*";
    } else {
      $select .= implode(',', $this->_selectColumns);
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
      $limitClause = 'LIMIT ' . $this->_limit;

      if ($this->_offset !== null) {
        $limitClause .= ' OFFSET ' . $this->_offset;
      }

      $clauses[] = $limitClause;
    }

    return implode(' ', $clauses);
  }

  /**
   * Add a between condition to the statement's WHERE clause.
   *
   * @param string $column The column to use as the left hand side of the
   *   expression. If the criteria contains any joins then the column may need
   *   to be fully qualified.
   * @param mixed $value1 The first end point of the desired range, inclusive.
   * @param mixed $value2 The second end point of the desired range, inclusive.
   * @return $this
   */
  public function addBetween($column, $value1, $value2) {
    if ($value1 === null || $value2 === null) {
      throw new Exception('BETWEEN end points cannot be NULL');
    }

    $escCol = self::_escapeFieldName($column);

    $idx = count($this->_params);
    $paramName1 = ":param$idx";
    $this->_params[$paramName] = $value1;

    $idx = count($this->_params);
    $paramName2 = ":param$idx";
    $this->_params[$paramName] = $value2;

    $this->_conditions[] = "$escCol BETWEEN $paramName1 AND $paramName2";

    return $this;
  }

  /**
   * Add an equals (=) condition to the statement's WHERE clause.
   *
   * @param string $column The column to use as the left hand side of the
   *   expression.  If the criteria contains any joins then the column must be
   *   fully qualified.
   * @param mixed $value The value to use as the right hand side of the
   *   expression.  This value will not be used directly in the statement but
   *   will instead be used as a value for parameterized statement.
   * @return $this
   */
  public function addEquals($column, $value) {
    return $this->addPredicate($column, $value, self::OP_EQUALS);
  }

  /**
   * Add an IN predicate to the statement's WHERE clause.
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

    $escCol = self::_escapeFieldName($column);
    $paramNames = Array();

    $idx = count($this->_params);
    foreach ($values AS $val) {
      $paramName = ":param$idx";
      $paramNames[] = $paramName;
      $this->_params[$paramName] = $val;
      $idx++;
    }
    $this->_conditions[] = "$escCol IN (" . implode(',', $paramNames) . ")";

    return $this;
  }

  /**
   * Add an IS NOT NULL predicate to the statement's WHERE clause.
   *
   * @param string $column The colum to evaluate as not null
   * @return $this
   */
  public function addIsNotNull($column) {
    $escCol = self::_escapeFieldName($column);
    $this->_conditions[] = "$escCol IS NOT NULL";

    return $this;
  }

  /**
   * Add an IS NULL predicate to the statement's WHERE clause.
   *
   * @param string $column The column to evaluate as null
   * @return $this
   */
  public function addIsNull($column) {
    $escCol = self::_escapeFieldName($column);
    $this->_conditions[] = "$escCol IS NULL";

    return $this;
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
    $escaped = self::_escapeFieldName($table);
    if ($rhs !== null) {
      $this->_joins[] = "JOIN $table ON \${table}.$lhs = $escaped.$rhs";
    } else {
      $this->_joins[] = "JOIN $table USING ($lhs)";
    }

    return $this;
  }

  /**
   * Add a WHERE condition using a LIKE operator.
   *
   * @param string $column The column to compare
   * @param mixed $value The value to compare against.  If an array then a
   *   tuple of OR conditions will be created, one for each value in the array.
   */
  public function addLike($column, $value) {
    return $this->addPredicate($column, $value, self::OP_LIKE);
  }

  /**
   * Add a Predicate to the statement's WHERE clause.
   *
   * @param string $column The column to use as the left hand side of the
   *   expression.  If the criteria contains any joins then the column must be
   *   fully qualified.
   * @param mixed $value The value to use as the right hand side of the
   *   expression.  This value will not be used directly in the statement but
   *   will instead by used as a value in a parameterized statement.  If the
   *   value is an array then a tuple of OR predicates is created.
   * @param string $op The operator for the predicate.  Must be one of the
   *   supported operators, defined by the OP_* constants.
   * @param string $transform A function which returns a transformation to
   *   apply to the preciates column. [Optional]
   * @return $this
   */
  public function addPredicate($column, $value, $op, $transform = null) {
    // Validate the given operator
    if (!in_array($op, self::$_ops)) {
      $this->_throwInvalidOperatorException($op);
      return; // This won't be reached since the above throws an exception
    }

    // Validate the transform, if given
    if ($transform !== null) {
      if (!in_array($transform, self::$_transforms)) {
        throw self::_newInvalidTransformationException($transform);
      }
    }

    // If the given value is null the delegate the the appropriate null value
    // method.  If the operator does not make sense for a null value then throw
    // an exception.
    if ($value === null) {
      if ($op === self::OP_EQUALS) {
        return $this->addIsNull($column);
      } else if ($op === self::OP_NOT_EQUALS) {
        return $this->addIsNoNull($column);
      } else {
        throw new Exception("Invalid predicate: $op with NULL value");
      }
    }

    // Normalize booleans to their INT equivalent as this is how they are stored
    // in the database
    if ($value === true) {
      $value = 1;
    }
    if ($value === false) {
      $value = 0;
    }

    $escCol = self::_escapeFieldName($column);
    if ($transform !== null) {
      $escCol = $transform($escCol);
    }

    if (is_array($value)) {
      $conditions = array();
      foreach ($value AS $val) {
        $paramName = $this->_getParam($val);

        $conditions[] = "$escCol $op $paramName";
      }

      $this->_conditions[] = '(' . implode(' OR ', $conditions) . ')';

    } else {
      $paramName = $this->_getParam($value);

      $this->_conditions[] = "$escCol $op $paramName";
    }

    return $this;
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

    return $this;
  }

  /**
   * Add a sort column, or list of sort columns.
   *
   * @param mixed $sorts The sorts to add
   */
  public function addSort($sort, $direction = 'asc') {
    if ($direction !== self::SORT_ASC && $direction !== self::SORT_DESC) {
      throw new Exception("Invalid sort direction: $direction");
    }

    if (!is_array($sort)) {
      $sort = explode(',', $sort);
    }

    foreach ($sort AS $col) {
      $this->_sorts[] = self::_escapeFieldName(trim($col)) . " $direction";
    }

    return $this;
  }

  /**
   * Clear the current select column list.  This is useful to reuse a criteria
   * object in order to get a total count for the criteria:
   *
   *    $c->clearSelects()
   *      ->addSelect('COUNT(*)')
   *      ->setLimit(null);
   */
  public function clearSelects() {
    $this->_selectColumns = null;
    return $this;
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
   * Setter for whether or not to include a distinct clause in the query.
   * If anything other than a boolean true is passed in then no DISTINCT clause
   * is added.
   *
   * @param boolean $distinct
   */
  public function setDistinct($distinct) {
    $this->_distinct = $distinct;

    return $this;
  }

  /**
   * Setter for the LIMIT clause of the SQL statement. Current limit can be
   * cleared with setLimit(null).
   *
   * @param integer $limit The maximum number of rows to return
   * @param integer $offset [optional] The offset from the beginning of the
   *   result set from which to select rows.
   */
  public function setLimit($limit, $offset = null) {
    if ($limit !== null && !is_int($limit)) {
      throw new Exception("Limit must be an integer: $limit");
    }
    $this->_limit = $limit;

    if ($offset !== null && !is_int($offset)) {
        throw new Exception("Offset must be an integer: $offset");
    }
    $this->_offset = $offset;

    return $this;
  }

  /**
   * Setter for the table that this criteria will query.  It is generally not
   * necessary to call this method manually as it will be set by the persister
   * executing the query.
   *
   * @param string $table
   */
  public function setTable($table) {
    $this->_table = self::_escapeFieldName($table);

    return $this;
  }

  /*
   * Get the name of the parameter (right hand side) to use in a WHERE clause
   * predicate.
   */
  private function _getParam($value) {
    if (is_object($value)) {
      if (in_array($value, self::$_funcs)) {
        return $value();
      } else {
        throw new Exception('Predicate value cannot be an object');
      }
    }
    
    $idx = count($this->_params);
    $paramName = ":param$idx";
    $this->_params[$paramName] = $value;

    return $paramName;
  }

  /*
   * Build an exception for users that have use addPredicate for an operator
   * with a dedicated method.
   */
  private function _notSupportedException($op, $method) {
    $msg = "$op is not supported by addPredicate(), use $method() instead";
    return new Exception($msg);
  }

  /*
   * Build and throw an exception for the given invalid operator.
   */
  private function _throwInvalidOperatorException($op) {
    $cmpOp = strtoupper($op);
    switch ($cmpOp) {

      case 'IN':
      throw $this->_notSupportedException('IN', 'addIn');

      case 'BETWEEN':
      throw $this->_notSupportedException('BETWEEN', 'addBetween');

      case 'IS NULL':
      throw $this->_notSupportedException('IS NULL', 'addIsNull');

      case 'IS NOT NULL':
      throw $this->_notSupportedException('IS NOT NULL', 'addIsNotNull');

      default:
      throw new Exception("Unrecognized operator: $op");
    }
  }
}
