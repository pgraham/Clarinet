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

use \Psr\Log\NullLogger;

/**
 * Base class for persister companions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class PersisterBase extends ModelCompanionBase
{

	protected function logQuery ($sql, $params) {
		if ($this->logger === null) {
			$this->logger = new NullLogger();
		}

		$paramsStr = $this->getParamsAsString($params);
		$this->logger->info("Executing query: $sql -- [ $paramsStr ]");
	}

	private function getParamsAsString($params) {
		if ($params === null) {
			return '';
		}

		if (!is_array($params)) {
			$paramsStr = (string) $params;
			$this->logger->warning(
				"Attempting to query with parameters that are not an array: $paramsStr"
			);
			return $paramsStr;
		}

		return implode(', ', array_map(function ($p) {
			return (string) $p;
		}, $params));
	}

}
