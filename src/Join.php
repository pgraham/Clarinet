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

use zpt\db\adapter\QueryAdapter;

/**
 * This class encapsulates a Criteria join.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Join
{

	/* The table to use on the left side of the join condition. */
	private $lhsTabl;

	/* The column to use on the left side of the join condiction. */
	private $lhsCol;

	/* The column to use on the right side of the join condition. */
	private $rhsCol;

	/* The join being joined into the result set. */
	private $table;

	/* The type of join. */
	private $type;

	/* QueryAdapter used to escape field names */
	private $queryAdapter;

	/**
	 * Constructor.
	 *
	 * @param string $table
	 * @param string $type
	 * @param QueryAdapter $queryAdapter
	 */
	public function __construct($table, $type, QueryAdapter $queryAdapter) {
		$this->table = $table;
		$this->type = $type;
		$this->queryAdapter = $queryAdapter;
	}

	/**
	 * Return the string representation of the join.
	 *
	 * @return string
	 */
	public function __toString() {
		$escTable = $this->queryAdapter->escapeField($this->table);
		$escLhsTbl = $this->queryAdapter->escapeField($this->lhsTabl);
		$escLhsCol = $this->queryAdapter->escapeField($this->lhsCol);
		$escRhsCol = $this->queryAdapter->escapeField($this->rhsCol);

		$parts = array("$this->type JOIN $escTable");
		if ($this->rhsCol !== null) {
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
		return $this->lhsTabl;
	}

	/**
	 * Getter for the table being joined.
	 *
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Setter for the column on the left side of the join condition.
	 *
	 * @param string $lhsCol
	 */
	public function setLhsColumn($lhsCol) {
		$this->lhsCol = $lhsCol;
	}

	/**
	 * Setter for the table on the left side of the join condition.
	 *
	 * @param string $lhsTbl
	 */
	public function setLhsTable($lhsTbl) {
		$this->lhsTabl = $lhsTbl;
	}

	/**
	 * Setter for the column on the right side of the join condition. The table
	 * in the right side of the condition will always be the table being joined.
	 *
	 * @param string $rhsCol
	 */
	public function setRhsColumn($rhsCol) {
		$this->rhsCol = $rhsCol;
	}

}
