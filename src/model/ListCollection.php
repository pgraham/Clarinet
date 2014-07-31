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
 * This class encapsulates a List collection mapping for a model.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ListCollection extends Collection
{

	/**
	 * The name of column that specifies the order of the items in the collection.
	 */
	protected $sequenceColumn;

	public function __construct(
		$property,
		$linkTable,
		$idColumn,
		$valueColumn,
		$sequenceColumn
	) {
			parent::__construct(
					'list',
					$property,
					$linkTable,
					$idColumn,
					$valueColumn
			);
			$this->sequenceColumn = $sequenceColumn;
	}

	public function asArray() {
		$base = parent::asArray();
		$base['seqCol'] = $this->sequenceColumn;
		return $base;
	}
}
