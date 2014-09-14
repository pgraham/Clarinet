<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License. The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\orm;

use zpt\db\adapter\MysqlQueryAdapter;
use zpt\db\adapter\QueryAdapter;
use Exception;

/**
 * This class encapsulates a set of criteria for a SELECT statement.
 *
 * @author Philip Graham <philip@zeptech.ca>
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

	/** Standard ASCending sort */
	const SORT_ASC = 'asc';
	/** Standard DESCending sort */
	const SORT_DESC = 'desc';
	/** ASCending sort but with NULL values at the end */
	const SORT_NULLS_LAST = 'nullslast';

	/*
	 * Array of supported predicate values that by-pass parameterization.  The
	 * values are defined as lambas so that they can be distinguished from a
	 * literal value named the same as the function.
	 */
	private static $funcs;

	/* Whether or not the supported function values are initialized. */
	private static $initialized = false;

	/*
	 * Array of operators supported by the addPredicate() method.  This array only
	 * contains operators that have a single right hand side value.  Other
	 * operators (IN, BETWEEN, IS NULL and IS NOT NULL) have their own methods.
	 */
	private static $ops = array(
		self::OP_EQUALS,
		self::OP_NOT_EQUALS,
		self::OP_GREATER_THAN,
		self::OP_GREATER_EQUALS,
		self::OP_LESS_THAN,
		self::OP_LESS_EQUALS,
		self::OP_LIKE
	);

	/*
	 * Array of valid sort directions.
	 */
	private static $availSorts = array(
		self::SORT_ASC,
		self::SORT_DESC,
		self::SORT_NULLS_LAST
	);

	/*
	 * Array of support column transformation values that allow columns to be
	 * modified as part of a predicate without being backticked (`).
	 */
	private static $transforms;

	/**
	 * Getter for the function lambda that performs the requested transformation.
	 * TODO Separate Column Transformation into its own class with a protected
	 *      constructor. This will allow parameterized transformations,
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
		if (!array_key_exists($cmp, self::$transforms)) {
			throw self::newInvalidTransformationException($transformation);
		}

		return self::$transforms[$cmp];
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
		if (!array_key_exists($name, self::$funcs)) {
			throw new Exception("Unrecognized predicate function: $func");
		}

		return self::$funcs[$name];
	}

	/**
	 * Static initializer.
	 */
	protected static function init() {
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;

		self::$funcs = array();

		self::$funcs['CURRENT_DATE'] = function () {
			return 'CURRENT_DATE';
		};

		self::$funcs['CURRENT_TIME'] = function () {
			return 'CURRENT_TIME';
		};

		self::$funcs['CURRENT_TIMESTAMP'] = function () {
			return 'CURRENT_TIMESTAMP';
		};

		self::$transforms = array();

		self::$transforms['EXTRACT_DATE'] = function ($escCol) {
			return "DATE($escCol)";
		};
	}

	/* Build and throw an exception for the given invalid cast type. */
	private static function newInvalidTransformationException($transform) {
		return new Exception("Invalid transformation: $transform");
	}

	/*
	 * ===========================================================================
	 * Instance
	 * ===========================================================================
	 */

	/* Set of expressions that are AND'd together to create the criteria */
	private $conditions = Array();

	/* The query's distinct clause */
	private $distinct = false;

	/* Set of join clauses */
	private $joins = Array();

	/* Limit for the number of rows to query. */
	private $limit = null;

	/* Offset from the beginning of the result from which to select rows. */
	private $offset = null;

	/* Clauses are created with parameterized values, these are the parameters */
	private $params = Array();

	/*
	 * Set of select columns.  This will only be set to something other than null
	 * if there are joins added to the criteria.
	 */
	private $selectColumns = null;

	/* List of columns to sort by with their direction */
	private $sorts = array();

	/* The table to select from. */
	private $table = null;

	/**
	 * Create a new Criteria object.
	 *
	 * @param QueryAdapter $queryAdapter
	 *   Adapter used to output SQL specific to a specific database engine.
	 *   Defaults to a MysqlQueryAdapter to maintain backswards compatibility.
	 */
	public function __construct(QueryAdapter $queryAdapter = null) {
		if ($queryAdapter === null) {
			$queryAdapter = new MysqlQueryAdapter();
		}
		$this->queryAdapter = $queryAdapter;
	}

	/**
	 * Return the SQL representation of the criteria.  This will be a complete SQL
	 * statement.
	 *
	 * @return string
	 */
	public function __toString() {
		$clauses = Array();

		$select = 'SELECT ';
		if ($this->distinct === true) {
			$select .= 'DISTINCT ';
		}

		$escTable = $this->queryAdapter->escapeField($this->table);
		if ($this->selectColumns === null) {
			$select .= "$escTable.*";
		} else {
			$select .= implode(',', $this->selectColumns);
		}
		$clauses[] = $select;

		$clauses[] = "FROM $escTable";

		if (count($this->joins) > 0) {
			foreach ($this->joins AS $join) {
				if ($join->getLhsTable() === null) {
					$join->setLhsTable($this->table);
				}
			}
			$clauses[] = implode(' ', $this->joins);
		}

		if (count($this->conditions) > 0) {
			$clauses[] = 'WHERE ' . implode(' AND ', $this->conditions);
		}

		if (count($this->sorts) > 0) {
			$clauses[] = 'ORDER BY ' . implode(',', $this->sorts);
		}

		if ($this->limit !== null) {
			$limitClause = 'LIMIT ' . $this->limit;

			if ($this->offset !== null) {
				$limitClause .= ' OFFSET ' . $this->offset;
			}

			$clauses[] = $limitClause;
		}

		return implode(' ', $clauses);
	}

	/**
	 * Add a between condition to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The column to use as the left hand side of the
	 *   expression. If the criteria contains any joins then the column may need
	 *   to be fully qualified.
	 * @param mixed $value1
	 *   The first end point of the desired range, inclusive.
	 * @param mixed $value2
	 *   The second end point of the desired range, inclusive.
	 * @return $this
	 */
	public function addBetween($column, $value1, $value2) {
		if ($value1 === null || $value2 === null) {
			throw new Exception('BETWEEN end points cannot be NULL');
		}

		$escCol = $this->queryAdapter->escapeField($column);

		$idx = count($this->params);
		$paramName1 = ":param$idx";
		$this->params[$paramName] = $value1;

		$idx = count($this->params);
		$paramName2 = ":param$idx";
		$this->params[$paramName] = $value2;

		$this->conditions[] = "$escCol BETWEEN $paramName1 AND $paramName2";

		return $this;
	}

	/**
	 * Add an equals (=) condition to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The column to use as the left hand side of the expression. If the
	 *   criteria contains any joins then the column must be fully qualified.
	 * @param mixed $value
	 *   The value to use as the right hand side of the expression. This value
	 *   will not be used directly in the statement but will instead be used as a
	 *   value for parameterized statement.
	 * @return $this
	 */
	public function addEquals($column, $value) {
		return $this->addPredicate($column, $value, self::OP_EQUALS);
	}

	/**
	 * Add an IN predicate to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The column to use as the left hand side of the expression.
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

		$escCol = $this->queryAdapter->escapeField($column);
		$paramNames = Array();

		$idx = count($this->params);
		foreach ($values AS $val) {
			$paramName = ":param$idx";
			$paramNames[] = $paramName;
			$this->params[$paramName] = $val;
			$idx++;
		}
		$this->conditions[] = "$escCol IN (" . implode(',', $paramNames) . ")";

		return $this;
	}

	/**
	 * Add an INNER JOIN clause to the criteria. The name of the table gets
	 * substituted at the time the criteria is transformed into an SQL string.
	 *
	 * @param string $table
	 *   The name of the table to JOIN with.
	 * @param string $lhs
	 *   The name of the column in the left side of the JOIN to join on.
	 * @param string $rhs
	 *   The name of the column in the right side of the JOIN to join on.
	 *   If null the USING syntax is used.
	 */
	public function addInnerJoin($table, $lhs, $rhs = null) {
		return $this->addJoin($table, 'INNER', $lhs, $rhs);
	}

	/**
	 * Add an IS NOT NULL predicate to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The colum to evaluate as not null
	 * @return $this
	 */
	public function addIsNotNull($column) {
		$escCol = $this->queryAdapter->escapeField($column);
		$this->conditions[] = "$escCol IS NOT NULL";

		return $this;
	}

	/**
	 * Add an IS NULL predicate to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The column to evaluate as null
	 * @return $this
	 */
	public function addIsNull($column) {
		$escCol = $this->queryAdapter->escapeField($column);
		$this->conditions[] = "$escCol IS NULL";

		return $this;
	}

	/**
	 * Add a JOIN clause to the criteria. The name of the table gets substituted
	 * at the time the criteria is transformed into an SQL string. All joins are
	 * against the criteria's FROM table. In order to chain joins, use the
	 * chain() method of the JOIN object returned by this method:
	 *
	 *     $c = new Criteria($queryAdapter);
	 *     $c->setFrom('foo');
	 *     $c->addJoin('goo', 'INNER', 'goo_id', 'id');
	 *     $c->addJoin('hoo', 'INNER', 'hoo_id', 'id');
	 *
	 *     echo $c->__toString();
	 *     // SELECT * FROM foo
	 *     // JOIN goo ON foo.goo_id = goo.id
	 *     // JOIN hoo ON foo.hoo_id = hoo.id
	 *
	 *     vs.
	 *     ---
	 *
	 *     $c = new Criteria($queryAdapter);
	 *     $c->setFrom('foo');
	 *     $c->addJoin('goo', 'INNER', 'goo_id', 'id')
	 *         ->chain('hoo', 'INNER', 'hoo_id', id');
	 *
	 *     echo $c->__toString();
	 *     // SELECT * FROM foo
	 *     // JOIN goo ON foo.goo_id = goo.id
	 *     // JOIN hoo ON goo.hoo_id = hoo.id
	 *
	 * @param string $table
	 *   The name of the table to JOIN with.
	 * @param string $type
	 *   The type of join to perform.
	 * @param string $lhs
	 *   The name of the column in the left side of the JOIN to join on.
	 * @param string $rhs
	 *   The name of the column in the right side of the JOIN to join on. If null
	 *   the USING syntax is used.
	 * @return $this
	 */
	public function addJoin($table, $type, $lhs, $rhs = null) {
		$join = new Join($table, $type, $this->queryAdapter);
		if ($rhs !== null) {
			$join->setRhsColumn($rhs);
			$join->setLhsColumn($lhs);
		} else {
			$join->setLhsColumn($lhs);
		}
		$this->joins[] = $join;

		return $this;
	}

	/**
	 * Add a LEFT JOIN clause to the criteria. The name of the table gets
	 * substituted at the time the criteria is transformed into an SQL string.
	 *
	 * @param string $table
	 *   The name of the table to JOIN with.
	 * @param string $lhs
	 *   The name of the column in the left side of the JOIN to join on.
	 * @param string $rhs
	 *   The name of the column in the right side of the JOIN to join on. If null
	 *   the USING syntax is used.
	 */
	public function addLeftJoin($table, $lhs, $rhs = null) {
		return $this->addJoin($table, 'LEFT', $lhs, $rhs);
	}

	/**
	 * Add a WHERE condition using a LIKE operator.
	 *
	 * @param string $column
	 *   The column to compare
	 * @param mixed $value
	 *   The value to compare against.	If an array then a tuple of OR conditions
	 *   will be created, one for each value in the array.
	 */
	public function addLike($column, $value) {
		return $this->addPredicate($column, $value, self::OP_LIKE);
	}

	/**
	 * Add a Predicate to the statement's WHERE clause.
	 *
	 * @param string $column
	 *   The column to use as the left hand side of the expression. If the
	 *   criteria contains any joins then the column must be fully qualified.
	 * @param mixed $value
	 *   The value to use as the right hand side of the expression. This value
	 *   will not be used directly in the statement but will instead by used as a
	 *   value in a parameterized statement. If the value is an array then a tuple
	 *   of OR predicates is created.
	 * @param string $op
	 *   The operator for the predicate. Must be one of the supported operators,
	 *   defined by the OP_* constants.
	 * @param string $transform
	 *   A function which returns a transformation to apply to the predicates
	 *   column.
	 * @return $this
	 */
	public function addPredicate($column, $value, $op, $transform = null) {
		// Validate the given operator
		if (!in_array($op, self::$ops)) {
			$this->throwInvalidOperatorException($op);
			return; // This won't be reached since the above throws an exception
		}

		// Validate the transform, if given
		if ($transform !== null) {
			if (!in_array($transform, self::$transforms)) {
				throw self::newInvalidTransformationException($transform);
			}
		}

		// If the given value is null then delegate to the appropriate null value
		// method. If the operator does not make sense for a null value then throw
		// an exception.
		if ($value === null) {
			if ($op === self::OP_EQUALS) {
				return $this->addIsNull($column);
			} else if ($op === self::OP_NOT_EQUALS) {
				return $this->addIsNotNull($column);
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

		$escCol = $this->queryAdapter->escapeField($column);
		if ($transform !== null) {
			$escCol = $transform($escCol);
		}

		if (is_array($value)) {
			$conditions = array();
			foreach ($value AS $val) {
				$paramName = $this->getParam($val);

				$conditions[] = "$escCol $op $paramName";
			}

			$this->conditions[] = '(' . implode(' OR ', $conditions) . ')';

		} else {
			$paramName = $this->getParam($value);

			$this->conditions[] = "$escCol $op $paramName";
		}

		return $this;
	}

	/**
	 * Add a select column.
	 *
	 * @param string $select The name of the column to select.
	 */
	public function addSelect($select) {
		if ($this->selectColumns === null) {
			$this->selectColumns = array();
		}
		$this->selectColumns[] = $this->queryAdapter->escapeField($select);

		return $this;
	}

	/**
	 * Add a sort column, or list of sort columns.
	 *
	 * @param mixed $sorts The sorts to add
	 */
	public function addSort($sort, $direction = 'asc') {
		$direction = strtolower($direction);
		if (!in_array($direction, self::$availSorts)) {
			throw new Exception("Invalid sort direction: $direction");
		}

		if (!is_array($sort)) {
			$sort = explode(',', $sort);
		}

		foreach ($sort AS $col) {
			$escCol = $this->queryAdapter->escapeField(trim($col));
			if ($direction === self::SORT_NULLS_LAST) {

				//$this->sorts[] = "CASE $escCol WHEN NULL THEN 1 ELSE 0 END";
				$this->sorts[] = "ISNULL($escCol)";
				$this->sorts[] = "$escCol";
			} else {
				$this->sorts[] =  "$escCol $direction";
			}
		}

		return $this;
	}

	/**
	 * Chain an INNER join to the previously added join.
	 *
	 * @param string $table
	 * @param string $lhs
	 * @param string $rhs
	 */
	public function chainInnerJoin($table, $lhs, $rhs = null) {
		return $this->chainJoin('INNER', $table, $lhs, $rhs);
	}

	/**
	 * Chain a join to the join previously added to the criteria.  I.e, the table
	 * on the left side of the join will be the table from the right side of
	 * the previously added join.
	 *
	 * @param string $type
	 * @param string $table
	 * @param string $lhs
	 * @param string $rhs
	 */
	public function chainJoin($type, $table, $lhs, $rhs = null) {
		$this->addJoin($table, $type, $lhs, $rhs);

		$numJoins = count($this->joins);
		$chained = $this->joins[$numJoins - 1];
		$chainedTo = $this->joins[$numJoins - 2];

		$chained->setLhsTable($chainedTo->getTable());

		return $this;
	}

	/**
	 * Clear the current select column list.
	 */
	public function clearSelects() {
		$this->selectColumns = null;
		return $this;
	}

	/**
	 * Getter for the values for any parameterized values in the criteria's
	 * SQL representation.
	 *
	 * @return Array
	 */
	public function getParameters() {
		return $this->params;
	}

	/**
	 * Getter for the name of the table that this criteria will query.
	 *
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Clear the select list and add a select column for COUNT(*)
	 */
	public function selectCount() {
		$this->selectColumns = array ( 'COUNT(*)' );
		return $this;
	}

	/**
	 * Setter for whether or not to include a distinct clause in the query.
	 * If anything other than a boolean true is passed in then no DISTINCT clause
	 * is added.
	 *
	 * @param boolean $distinct
	 */
	public function setDistinct($distinct) {
		$this->distinct = $distinct;

		return $this;
	}

	/**
	 * Setter for the LIMIT clause of the SQL statement. Current limit can be
	 * cleared with setLimit(null).
	 *
	 * @param integer $limit
	 *   The maximum number of rows to return
	 * @param integer $offset
	 *   The offset from the beginning of the result set from which to select
	 *   rows.
	 */
	public function setLimit($limit, $offset = null) {
		if ($limit !== null && !is_int($limit)) {
			throw new Exception("Limit must be an integer: $limit");
		}
		$this->limit = $limit;

		if ($offset !== null && !is_int($offset)) {
				throw new Exception("Offset must be an integer: $offset");
		}
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Setter for the {@link QueryAdapter} to use when rendering queries using
	 * features specific to a particular database engine.
	 *
	 * @param QueryAdapter $queryAdapter
	 */
	public function setQueryAdapter(QueryAdapter $queryAdapter) {
		$this->queryAdapter = $queryAdapter;
	}

	/**
	 * Setter for the table that this criteria will query. It is generally not
	 * necessary to call this method manually as it will be set by the persister
	 * executing the query.
	 *
	 * @param string $table
	 */
	public function setTable($table) {
		$this->table = $table;

		return $this;
	}

	/*
	 * Get the name of the parameter (right hand side) to use in a WHERE clause
	 * predicate.
	 */
	private function getParam($value) {
		if (is_object($value)) {
			if (in_array($value, self::$funcs)) {
				return $value();
			} else {
				throw new Exception('Predicate value cannot be an object');
			}
		}

		$idx = count($this->params);
		$paramName = ":param$idx";
		$this->params[$paramName] = $value;

		return $paramName;
	}

	/*
	 * Build an exception for users that have use addPredicate for an operator
	 * with a dedicated method.
	 */
	private function notSupportedException($op, $method) {
		$msg = "$op is not supported by addPredicate(), use $method() instead";
		return new Exception($msg);
	}

	/*
	 * Build and throw an exception for the given invalid operator.
	 */
	private function throwInvalidOperatorException($op) {
		$cmpOp = strtoupper($op);
		switch ($cmpOp) {

			case 'IN':
			throw $this->notSupportedException('IN', 'addIn');

			case 'BETWEEN':
			throw $this->notSupportedException('BETWEEN', 'addBetween');

			case 'IS NULL':
			throw $this->notSupportedException('IS NULL', 'addIsNull');

			case 'IS NOT NULL':
			throw $this->notSupportedException('IS NOT NULL', 'addIsNotNull');

			default:
			throw new Exception("Unrecognized operator: $op");
		}
	}
}
