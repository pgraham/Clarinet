<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\model;

use zpt\anno\AnnotationFactory;
use zpt\orm\model\parser\ModelParser;

/**
 * Factory class for {@link Model} objects.
 *
 * Combines the functionality of the {@link ModelParser} and {@link ModelCache}.
 */
class ModelFactory
{

	private $modelCache;
	private $modelParser;

	public function __construct(AnnotationFactory $annotationFactory = null) {
		if ($annotationFactory === null) {
			$annotationFactory = new AnnotationFactory();
		}

		$this->modelCache = new ModelCache();
		$this->modelParser = new ModelParser($this->modelCache, $annotationFactory);
	}

	public function get($classDef) {
		$model = $this->modelCache->get($classDef);

		if ($model === null) {
			$model = $this->modelParser->parse($classDef);
			$this->modelCache->set($classDef, $model);
		}

		return $model;
	}

	public function getModelCache() {
		return $this->modelCache;
	}
}
