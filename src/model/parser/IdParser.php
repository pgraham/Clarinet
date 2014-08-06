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
use \Exception;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * This class is responsible for parsing the ID column for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class IdParser extends BaseMethodParser
{

  /**
   * Return a Property object for the ID column of the given model class.
   *
   * @param ReflectionClass $class
   * @return Property
   */
  public function parse(ReflectionClass $class)
  {
    $methods = $class->getMethods();

    $id = null;
    foreach ($methods as $method) {
      $annos = $this->annotationFactory->get($method);

      if ($annos->isAnnotatedWith('id')) {
        if ($id !== null) {
          throw new Exception("{$class->getName()} has multiple ID columns.");
        }

        $id = $this->parseId($method, $annos);

        // Continue to loop to verify that only one ID column is defined.
      }
    }

    return $id;
  }

  public function parseId(ReflectionMethod $method, $annos)
  {
    $propertyName = $this->getPropertyName($method);

    if (isset($annos['column'])) {
      $column = $annos['column']['name'];
    } else {
      $column = $this->namingStrategy->getColumnName($propertyName);
    }

    $property = new Property($propertyName, $column);

    if ($annos->isAnnotatedWith('type')) {
      $property->setType($annos['type']);
    } else {
      $property->setType(Property::TYPE_INTEGER);
    }
    return $property;
  }

}

