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
 * Interface for relationship representations.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Relationship {

	const TYPE_MANYTOMANY = 'many-to-many';
	const TYPE_MANYTOONE  = 'many-to-one';
	const TYPE_ONETOMANY  = 'one-to-many';

	/**
	 * This method is responsible for returning the clarinet\model\Model object
	 * for the entity on the left side of the relationship.
	 *
	 * @return Model
	 */
	public function getLhs();

	/**
	 * This method is responsible for returning the name of the property on the
	 * left side that contains this relationship.
	 *
	 * @return string
	 */
	public function getLhsProperty();

	/**
	 * This method is responsible for returning the clarinet\model\Model object
	 * for the entity on the right side of the relationship.
	 *
	 * @return Model
	 */
	public function getRhs();
}
