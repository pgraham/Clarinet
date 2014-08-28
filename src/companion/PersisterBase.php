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

use zpt\opal\CompanionAwareInterface;
use zpt\opal\CompanionLoaderFactory;
use zpt\orm\companion\PersisterCompanionDirector;
use zpt\orm\companion\TransformerCompanionDirector;
use zpt\orm\companion\ValidatorCompanionDirector;
use Psr\Log\NullLogger;

/**
 * Base class for persister companions.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class PersisterBase extends ModelCompanionBase
	implements CompanionAwareInterface
{

	protected $persisterLoader;
	protected $transformerLoader;
	protected $validatorLoader;
	protected $queryBuilderLoader;

	protected $cache = [];

	/**
	 * Clear the cache. If an id is provided, only the entity with the given id
	 * is cleared.	This will happen when an entity at the many side of an
	 * un-mirrored one-to-many relationship is updated to ensure that it does not
	 * have a stale id for the one side of the relationship. The entire cache is
	 * generally only cleared during testing.
	 *
	 * @param mixed $id
	 */
	public function clearCache($id = null) {
		if ($id === null) {
			$this->cache = array();
		} else if (is_array($id)) {
			foreach ($id as $idx) {
				unset($this->cache[$idx]);
			}
		} else {
			unset($this->cache[$id]);
		}
	}

	/**
	 * {@link CompanionAwareInterface#setCompanionLoaderFactory(CompanionLoaderFactory)}
	 * implementation. Creates required {@link CompanionLoader}s.
	 *
	 * @param CompanionLoaderFactory $factory
	 */
	public function setCompanionLoaderFactory(CompanionLoaderFactory $factory) {
		$persisterLoader = $factory->get(new PersisterCompanionDirector());
		$transformerLoader = $factory->get(new TransformerCompanionDirector());
		$validatorLoader = $factory->get(new ValidatorCompanionDirector());

		$this->persisterLoader = $persisterLoader;
		$this->transformerLoader = $transformerLoader;
		$this->validatorLoader = $validatorLoader;
	}

	protected function getPersister($className) {
		return $this->persisterLoader->get($className);
	}

	protected function getTransformer($className) {
		return $this->transformerLoader->get($className);
	}

	protected function getValidator($className) {
		return $this->validatorLoader->get($className);
	}

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

		$paramsStr = [];
		foreach ($params as $k => $v) {
			$paramsStr[] = "$k: $v";
		}
		return implode(', ', $paramsStr);
	}

}
