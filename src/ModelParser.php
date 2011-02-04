<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package clarinet
 */
namespace clarinet;

use \ReflectionClass;
use \ReflectionException;

use \reed\util\ReflectionHelper;

/**
 * This class parses a model class' information into an array structure that is
 * expected by the generator classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class ModelParser {

  public static function parse($className) {
    try {
      $class = new ReflectionClass($className);
    } catch (ReflectionException $e) {
      throw new Exception("Unable to reflect $className", $e);
    }

    $classDocComment = $class->getDocComment();
    $classAnnotations = ReflectionHelper::getAnnotations($classDocComment);
    if (!isset($classAnnotations['entity'])) {
      throw new Exception("Clarinet can only generate classes for models"
        . " that define an @Entity(table = <tablename>) annotation in the"
        . " class level doc comment");
    }

    if (!isset($classAnnotations['entity']['table'])) {
      throw new Exception("@Entity annotation must define a table name:"
        . " @Entity(table = <tablename>)");
    }

    $properties = Array();
    $idColumn = null;

    $methods = $class->getMethods();
    foreach ($methods AS $method) {
      $methodDocComment = $method->getDocComment();
      $methodAnnotations = ReflectionHelper::getAnnotations($methodDocComment);

      if (!isset($methodAnnotations['column'])) {
        continue;
      }

      if (isset($methodAnnotations['id']) && $idColumn !== null) {
        throw new Exception("Entity has more than one id column defined");
      }

      $methodName = $method->getName();
      if (substr($methodName, 0, 3) !== 'get') {
        throw new Exception("Only getters can be marked as columns");
      }

      $propertyName = substr($methodName, 3);
      if (!$class->hasMethod("set$propertyName")) {
        throw new Exception("Column getters must have a matching setter");
      }

      $methodInfo = Array
      (
        'name'   => $propertyName,
        'column' => $methodAnnotations['column']['name']
      );

      if (isset($methodAnnotations['id'])) {
        $idColumn = $methodInfo;
      } else {
        $properties[] = $methodInfo;
      }
    }

    if ($idColumn === null) {
      throw new Exception("@Entity implementation does not define an Id column."
        . "  Use the @Id annotation to denote a column as the id column.");
    }

    if (count($properties) == 0) {
      throw new Exception("@Entity implementation $className does not define"
        . " any columns");
    }

    $entityInfo = array(
      'class'      => $className,
      'actor'      => str_replace('\\', '_', $className),
      'table'      => $classAnnotations['entity']['table'],
      'id'         => $idColumn,
      'properties' => $properties
    );
    return $entityInfo;
  }
}
