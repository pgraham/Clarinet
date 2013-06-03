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
 */
namespace zpt\orm\test;

use \zpt\anno\AnnotationFactory;
use \zpt\orm\companion\PersisterGenerator;
use \zpt\orm\companion\TransformerGenerator;
use \zpt\orm\companion\ValidatorGenerator;
use \zpt\orm\model\parser\DefaultNamingStrategy;
use \zpt\orm\model\parser\ModelParser;
use \zpt\orm\model\ModelCache;
use \DirectoryIterator;
use \ReflectionClass;

/**
 * This class generates actors for all mock entities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Generator {

  private static $annoFactory;
  private static $modelCache;

  /**
   * Iterator over all files in the mock directory and create actors for any
   * entity classes.
   */
  public static function generate() {
    if (self::$annoFactory === null || self::$modelCache === null) {
      self::initDeps();
    }

    $mockDir = __DIR__ . '/mock';

    $persisterGen = new PersisterGenerator($mockDir . '/gen');
    $persisterGen->setModelCache(self::$modelCache);
    $transformerGen = new TransformerGenerator($mockDir . '/gen');
    $transformerGen->setModelCache(self::$modelCache);
    $validatorGen = new ValidatorGenerator($mockDir . '/gen');
    $validatorGen->setModelCache(self::$modelCache);

    $dir = new DirectoryIterator($mockDir);
    foreach ($dir AS $file) {
      if ($file->isDot() || $file->isDir()) {
        continue;
      }

      $filename = $file->getFilename();
      if (substr($filename, -4) != '.php') {
        continue;
      }
      
      $className = "zpt\\orm\\test\\mock\\" . substr($filename, 0, -4);
      $refClass = new ReflectionClass($className);
      $annotations = self::$annoFactory->get($refClass);

      if (isset($annotations['entity'])) {
        $persisterGen->generate($className);  
        $transformerGen->generate($className);
        $validatorGen->generate($className);
      }
    }
  }

  private static function initDeps() {
    $annotationFactory = new AnnotationFactory();
    $namingStrategy = new DefaultNamingStrategy();
    $modelParser = new ModelParser();
    $modelCache = new ModelCache();

    $namingStrategy->setAnnotationFactory($annotationFactory);

    $modelParser->setModelCache($modelCache);
    $modelParser->setAnnotationFactory($annotationFactory);
    $modelParser->setNamingStrategy($namingStrategy);
    $modelParser->init();

    $modelCache->setModelParser($modelParser);

    self::$annoFactory = $annotationFactory;
    self::$modelCache = $modelCache;
  }
}
