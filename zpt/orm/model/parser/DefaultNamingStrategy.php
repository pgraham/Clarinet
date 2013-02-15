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

use \zeptech\anno\AnnotationFactory;
use \zeptech\orm\generator\model\Model;
use \zpt\util\StringUtils;
use \ReflectionMethod;

/**
 * Default clarinet naming strategy.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class DefaultNamingStrategy implements NamingStrategy
{

  private $annotationFactory;

  /**
   * Get the name of the link table for a collection property.  This composed
   * of the default table name for a class followed by an '_' then the default
   * column name of the property suffixed with '_collection'.
   *
   * @param string $className
   * @param string $property
   * @return string
   */
  public function getCollectionLink(Model $model, $property)
  {
    return $model->getTable() . '_' .  $this->getColumnName($property) .
      '_collection';
  }

  /**
   * Get the default column name for the given mapping method.
   *
   * @param string $property
   * @return string
   */
  public function getColumnName($property)
  {
    return StringUtils::fromCamelCase($property);
  }

  /**
   * Get the default name for columns that link to entities of the specified
   * model.
   *
   * @param Model $model
   * @return string
   */
  public function getLinkColumn(Model $model)
  {
    return $model->getTable() . '_' . $model->getId()->getColumn();
  }

  /**
   * Generate the name for a table that links left side and right side entities.
   * This is the name of the table for the left side followed by an underscore,
   * '_', then the name of the right side table and suffix with '_link'.
   *
   * @param Model $lhs Left hand side model
   * @param Model $rhs Right hand side model
   * @return string
   */
  public function getLinkTable(Model $lhs, Model $rhs)
  {
    return $lhs->getTable() . '_' . $rhs->getTable() . '_link';
  }

  /**
   * Get the default table name for the given class name. If the class is
   * annotated with @Plural, the lowercased value will be used, otherwise a
   * rudimentary pluralization will be performed on the lower cased basename for
   * the class.
   *
   * @param string $className
   * @return string
   */
  public function getTableName($className)
  {
    $annotations = $this->annotationFactory->get($className);
    if (isset($annotations['plural'])) {
      return strtolower($annotations['plural']);
    }

    return $this->pluralize(strtolower($this->getClassBaseName($className)));
  }

  /* ===========================================================================
   * Dependency setters.
   * ------------------------------------------------------------------------ */

  public function setAnnotationFactory(AnnotationFactory $annotationFactory)
  {
    $this->annotationFactory = $annotationFactory;
  }

  /* ===========================================================================
   * Private helpers.
   * ------------------------------------------------------------------------ */

  /* Return a basename for the given classname */
  private function getClassBaseName($classname)
  {
    $namespaces = explode('\\', $className);
    $basename = array_pop($namespaces);

    $namespaces = explode('_', $basename);
    $basename = array_pop($namespaces);

    return $basename;
  }

  /*
   * Rudimentary pluralization function. Trailing 'y' is replaced with 'ies'
   * otherwise 's' is appended to the name
   */
  private function pluralize($name)
  {
    if (substr($name, -1) === 'y') {
      return substr($name, 0, -1) . 'ies';
    }

    return $name . 's';
  }

}

