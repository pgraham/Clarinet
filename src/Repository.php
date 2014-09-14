<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm;

use zpt\db\DatabaseConnection;
use zpt\opal\CompanionLoaderFactory;
use zpt\opal\Psr4Dir;
use zpt\orm\companion\PersisterCompanionDirector;
use zpt\orm\model\ModelFactory;

/**
 * This class encapsulates the entities available through a single database
 * connection.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Repository
{

	private $db;

	private $persisterLdr;
	private $transformerLdr;
	private $validatorLdr;
	private $msgGenLdr;
	private $qbLdr;

	/**
	 * Create a new Repository representing entities stored in the specified
	 * database.
	 *
	 * @param DatabaseConnection $db
	 * @param Psr4Dir $dynTarget
	 *   The PSR-4 prefix target directory where generated Companion sources are
	 *   found.
	 */
	public function __construct(DatabaseConnection $db, Psr4Dir $dynTarget) {
		$this->db = $db;

		$ldrFactory = new CompanionLoaderFactory($dynTarget);
		$this->persisterLdr = $ldrFactory->get('persister');
	}

	public function getDb() {
		return $this->db;
	}

	public function getPersister($model) {
		$persister = $this->persisterLdr->get($model, $this->db);
		return $persister;
	}

	public function getTransformer($model) {

	}

	public function getMessageGenerator($model) {

	}
}
