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
 * This interface is implemented by multiton objects that provide a unique
 * string identifier for each instance in the same runtime.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Identifiable {

	/**
	 * Getter for the instances string identifier.	Each instance of Identifiable
	 * in the same runtime must return a unique identifier.
	 *
	 * @return string
	 */
	public function getIdentifier();

}
