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
 * Base class for relationships.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class AbstractRelationship implements Relationship
{

	/** Model for the entity on the left side of the relationship */
	protected $lhs;

	/** Property in the left side entity that contains this relationship */
	protected $lhsPropery;

	/** Model for the entity on the right side of the relationship */
	protected $rhs;

	/* Identifiable instance's unique identifier. */
	private $identifier;

	/**
	 * Initiate a new relationship.
	 *
	 * @param Model $lhs
	 *   The model entity on the left side of the relationship.
	 * @param Model $rhs
	 *   The model entity on the right side of the relationship.
	 * @param string $lhsProperty
	 *   The name of the property on the left side that contains the relationship.
	 */
	protected function __construct(Model $lhs, Model $rhs, $lhsProperty) {
		$this->lhs = $lhs;
		$this->rhs = $rhs;
		$this->lhsProperty = $lhsProperty;

		$this->identifier = $lhs->getActor() . '-' . $rhs->getActor();
	}

	/**
	 * Getter for the unique name identifying this relationship.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->identifier;
	}

	/**
	 * Getter for the zpt\orm\model\Model object for the left side of the
	 * relationship.
	 *
	 * @return Model
	 */
	public function getLhs() {
		return $this->lhs;
	}

	/**
	 * Getter for the column on the left side that contains this relationship.
	 * Only the ManyToOne relationship returns a value for this method so the
	 * default is to return null
	 *
	 * @return null
	 */
	public function getLhsColumn() {
		return null;
	}

	/**
	 * Getter for the property on the left side that contains this relationship.
	 *
	 * @return string Property name.
	 */
	public function getLhsProperty() {
		return $this->lhsProperty;
	}

	/**
	 * Getter for the zpt\orm\model\Model object for the right side of the
	 * relationship.
	 *
	 * @return Model
	 */
	public function getRhs() {
		return $this->rhs;
	}

	/**
	 * Getter for the type of relationship.
	 *
	 * @return string
	 *   One of this class' TYPE_* constants.
	 */
	public function getType() {
		if ($this instanceof ManyToMany) {
			return Relationship::TYPE_MANYTOMANY;
		} else if ($this instanceof ManyToOne) {
			return Relationship::TYPE_MANYTOONE;
		} else if ($this instanceof OneToMany) {
			return Relationship::TYPE_ONETOMANY;
		} else {
			$class = get_class($this);
			assert("false /* Unrecognized Relationship implementation: $class */");
		}
	}
}
