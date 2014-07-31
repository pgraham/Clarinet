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
 * This class encapsulates a map collection ORM mapping for a model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class MapCollection extends Collection
{

	/** The name of the column that contains the key for each map item. */
	protected $keyColumn;

	public function __construct(
			$property,
			$linkTable,
			$idColumn,
			$valueColumn,
			$keyColumn
	) {
			parent::__construct(
					'map',
					$property,
					$linkTable,
					$idColumn,
					$valueColumn
			);
			$this->keyColumn = $keyColumn;
	}

	public function asArray() {
		$base = parent::asArray();
		$base['keyCol'] = $this->keyColumn;
		return $base;
	}

}
