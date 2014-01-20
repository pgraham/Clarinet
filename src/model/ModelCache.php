<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\orm\model;

use \zeptech\orm\generator\model\Model;
use \zpt\orm\model\parser\ModelParser;

/**
 * This class stores reference to parsed models.  Note that if it is possible
 * that a model in the cache is in an incomplete state due to its relationships
 * having not been fully parsed.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelCache {

  private $cache = array();

  private $modelParser;

  public function clear() {
    $this->cache = array();
  }

  public function get($className) {
    if (!$this->isCached($className)) {
      $this->modelParser->parse($className);
    }

    return $this->cache[$className];
  }

  public function isCached($className) {
    return isset($this->cache[$className]);
  }

  public function set($className, Model $model) {
    $this->cache[$className] = $model;
  }

  public function setModelParser(ModelParser $modelParser) {
    $this->modelParser = $modelParser;
  }
}
