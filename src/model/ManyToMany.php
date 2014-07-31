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
 * This class encapsulates a Many-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ManyToMany extends AbstractRelationship {

	private $linkTable;
	private $linkLhsId;
	private $linkRhsId;
	private $orderBy;
	private $fetchPolicy;

	/**
	 * Create a new Many-to-many relationship representation.  The left side of
	 * the relationship is implied by the Info object to which the relationship
	 * belongs.
	 *
	 * @param Model $lhs
	 *   The model on the left side of the relationship.
	 * @param Model $rhs
	 *   The model on the right side of the relationship.
	 * @param string $property
	 *   The name of the property that contains the related entity.
	 * @param string $linkTable
	 *   The name of the table that contains the mapping.
	 * @param string $linkLhsId
	 *   The name of the column in the mapping table that contains the id of the
	 *   entity on the left side of the relationship.
	 * @param string $linkRhsId
	 *   The name of the column in the mapping table that contains the id of the
	 *   entity on the right side of the relationship.
	 */
	public function __construct($lhs, $rhs, $property, $linkTable, $linkLhsId,
		$linkRhsId)
	{
		parent::__construct($lhs, $rhs, $property);
		$this->linkTable = $linkTable;
		$this->linkLhsId = $linkLhsId;
		$this->linkRhsId = $linkRhsId;
	}

	/**
	 * Getter for the relationship's fetch policy.	Will be either 'lazy' or
	 * 'eager'.  This can be specified as the 'fetch' parameter of the
	 * relationship's declaring annotation.
	 *
	 * @return string
	 *   Either 'lazy' or 'eager'
	 */
	public function getFetchPolicy() {
		return $this->fetchPolicy;
	}

	/**
	 * Get the name of the column in the link table that contains the left side
	 * entity id.
	 *
	 * @return string
	 *   Link table left side id column.
	 */
	public function getLinkLhsId() {
		return $this->linkLhsId;
	}

	/**
	 * Get the name of the column in the link table that contains the right side
	 * entity id.
	 *
	 * @return string
	 *   Link table right side id column.
	 */
	public function getLinkRhsId() {
		return $this->linkRhsId;
	}

	/**
	 * Get the name of the relationship's link table.
	 *
	 * @return string
	 *   Link table name.
	 */
	public function getLinkTable() {
		return $this->linkTable;
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
	 * Setter for the relationship's fetch policy, can be either 'lazy' or
	 * 'eager'.  This is currently only honoured by the transformer.	All
	 * relationships are retrieved eagerly by the persister, but this will change
	 * in the future when proxies are introduced.
	 *
	 * @param string $fetchPolicy
	 *   Either 'lazy' or 'eager'
	 */
	public function setFetchPolicy($fetchPolicy) {
		if (!in_array($fetchPolicy, array('lazy', 'eager'))) {
			throw new Exception("Invalid fetch policy: $fetchPolicy");
		}
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
