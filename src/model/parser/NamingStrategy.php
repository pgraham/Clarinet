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

use \zeptech\orm\generator\model\Model;
use \ReflectionMethod;

/**
 * Interface for classes that generate default mapping names.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface NamingStrategy
{

  /**
   * Get the name of the link table for a collection property.
   *
   * @param string $className The name of the model to which the collection
   *                          belongs.
   * @param string $property  The name of the property that contains the
   *                          collection.
   * @return string
   */
  public function getCollectionLink(Model $model, $property);

  /**
   * Get the name for the column mapped to by given model property method.
   *
   * @param string $property
   * @return string
   */
  public function getColumnName($property);

  /**
   * Generate the name for a column that links to entities of the given model.
   *
   * @param Model $model
   * @return string
   */
  public function getLinkColumn(Model $model);

  /**
   * Generate the name for a table that links left side and right side entities.
   * This is the name of the table for the left side followed by an underscore,
   * '_', then the name of the right side table and suffix with '_link'.
   *
   * @param Model $lhs Left hand side model
   * @param Model $rhs Right hand side model
   * @return string
   */
  public function getLinkTable(Model $lhs, Model $rhs);

  /**
   * Generate the name of the table mapped to by the given model class name.
   *
   * @param string $className
   * @return string
   */
  public function getTableName($className);

}

