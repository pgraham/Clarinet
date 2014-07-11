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

use \zpt\orm\model\Property;
use \ReflectionMethod;
use \Exception;

/**
 * This class is responsible for parsing the column mappings for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ColumnParser extends BaseMethodParser
{

  public function parse($class)
  {
    $methods = $class->getMethods();

    $columns = array();
    foreach ($methods as $method) {
      $annos = $this->annotationFactory->get($method);

      if ($annos->isAnnotatedWith('column')) {
        $columns[] = $this->parseColumn($method, $annos);
      }
    }

    return $columns;
  }

  public function parseColumn(ReflectionMethod $method, $annos)
  {
    $propertyName = $this->getPropertyName($method);

    if (is_string($annos['column'])) {
      $column = $annos['column'];
    } elseif (isset($annos['column']['name'])) {
      $column = $annos['column']['name'];
    } else {
      $column = $this->namingStrategy->getColumnName($propertyName);
    }

    $property = new Property($propertyName, $column);

    if ($annos->isAnnotatedWith('type')) {
      $property->setType($annos['type']);
    }

    if ($annos->isAnnotatedWith('enumerated')) {
      $values = $annos['enumerated']['values'];
      if (!is_array($values)) {
        throw new Exception(
          "{$method->getDeclaringClass()->getName()}::" .
          "{$method->getName()}: @Enumerated must contains a list of values."
        );
      }
      $property->setValues($values);
    }

    if ($annos->isAnnotatedWith('default')) {
      $property->setDefault($annos['default']);
    }

    return $property;
  }
}
