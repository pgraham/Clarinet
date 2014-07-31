<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\model;

/**
 * This class encapsulates a many-to-one relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ManyToOne extends AbstractRelationship
{

	private $lhsColumn;

	/**
	 * Creates a new Many-to-one relationship representation.
	 *
	 * @param Model $lhs
	 *   The model on the left side of the relationship.
	 * @param string $rhs
	 *   The model on the right side of the relationship.
	 * @param string $property
	 *   The name of the model's property that contains the relationship.
	 * @param string $lhsColumn
	 *   The name of the model table's column that contains the id of the related
	 *   entity.
	 */
	public function __construct($lhs, $rhs, $property, $lhsColumn) {
		parent::__construct($lhs, $rhs, $property);
		$this->lhsColumn = $lhsColumn;
	}

	/**
	 * Returns the name of the column that represents the relationship in the
	 * database
	 */
	public function getLhsColumn() {
		return $this->lhsColumn;
	}
}
