<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\orm\companion;

use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

/**
 * Base class for all Companion instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class ModelCompanionBase implements LoggerAwareInterface {
	use LoggerAwareTrait;

}
