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
 * This class encapsulates a set property mapping for a model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SetCollection extends Collection
{

	public function __construct($property, $linkTable, $idColumn $valueColumn) {
		parent::__construct(
			'set',
			$property,
			$linkTable,
			$idColumn,
			$valueColumn
		);
	}
}
