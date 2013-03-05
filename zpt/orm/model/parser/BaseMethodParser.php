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
namespace zpt\orm\model\parser;

use \zpt\anno\AnnotationFactory;
use \zpt\orm\model\ModelCache;
use \ReflectionMethod;

/**
 * Base class for all parsers that parse model methods for mapping information.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class BaseMethodParser
{

  protected $annotationFactory;
  protected $namingStrategy;
  protected $modelCache;

  public function __construct(
    AnnotationFactory $annotationFactory,
    NamingStrategy $namingStrategy,
    ModelCache $modelCache
  ) {
    $this->annotationFactory = $annotationFactory;
    $this->namingStrategy = $namingStrategy;
    $this->modelCache = $modelCache;
  }

  protected function getPropertyName(ReflectionMethod $method)
  {
    $name = $method->getName();
    if (substr($name, 0, 3) !== 'get') {
      throw new Exception("Method name for a column must be a getter: $name");
    }

    $propertyName = substr($name, 3);
    if (!$method->getDeclaringClass()->hasMethod("set$propertyName")) {
      throw new Exception("Columns must have a matching setter: $name");
    }

    return lcfirst($propertyName);
  }
}
