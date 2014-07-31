<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\model;

use Exception;

/**
 * This class encapsulates a one-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class OneToMany extends AbstractRelationship {

	private $rhsColumn;
	private $rhsProperty;
	private $orderBy;
	private $deleteOrphans = false;

	/**
	 * Creates a new one-to-many relationship representation.
	 *
	 * @param Model $lhs The model on the left side of the relationship.
	 * @param Model $rhs The model on the right side of the relationship.
	 * @param string $lhsProperty The name of the property in the left hand side
	 *	 entity that contains the related right hand side instances.
	 * @param string $rhsColumn The name of the column in the right side of the
	 *	 relationship that contains the id of the left side entity to which
	 *	 right side entities are related.
	 */
	public function __construct($lhs, $rhs, $lhsProperty, $rhsColumn) {
		parent::__construct($lhs, $rhs, $lhsProperty);
		$this->rhsColumn = $rhsColumn;
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
			$this->deleteOrphans = $deleteOrphans;
		}
		return $this->deleteOrphans;
	}

	/**
	 * Getter for the order by clause to use when retrieving related entities.
	 *
	 * @return array
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * Getter for the relationship's right side column.
	 *
	 * @return string The name of the column on the right side of the relationship
	 */
	public function getRhsColumn() {
		return $this->rhsColumn;
	}

	/**
	 * Setter for the order by clause to use when retrieving the related entities.
	 *
	 * @param string $column
	 * @param string $direction
	 */
	public function setOrderBy($column, $direction) {
		$this->orderBy = array(
			'col' => $column,
			'dir' => $direction
		);
	}
}
