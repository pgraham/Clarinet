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
use \zpt\opal\CompanionGenerator;
use \zpt\opal\Psr4Dir;
use \zpt\orm\companion\PersisterCompanionDirector;
use \zpt\orm\companion\TransformerCompanionDirector;
use \zpt\orm\companion\ValidatorCompanionDirector;
use \zpt\orm\model\ModelFactory;
use \zpt\util\NamespaceResolver;
use \DirectoryIterator;
use \ReflectionClass;

/**
 * This class generates actors for all mock entities.
 *
 * TODO Move the functionality of this class out of the test package so that it
 * is more widely available.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Generator {

  private static $annoFactory;
  private static $modelFactory;

  /**
   * Iterator over all files in specified namespace and creates actors for any
   * entity classes.
   */
  public static function generate(
    Psr4Dir $target = null,
    Psr4Dir $modelDir = null
  ) {
    if (self::$annoFactory === null || self::$modelFactory === null) {
      self::initDeps();
    }

    if ($target === null) {
      global $dynTarget;
      $target = $dynTarget;
    }

    if ($modelDir === null) {
      $modelDir = new Psr4Dir(__DIR__ . '/mock', 'zpt\orm\test\mock\\');
    }

    $persisterGen = new CompanionGenerator(
      new PersisterCompanionDirector(self::$modelFactory),
      $target
    );
    $transformerGen = new CompanionGenerator(
      new TransformerCompanionDirector(self::$modelFactory),
      $target
    );
    $validatorGen = new CompanionGenerator(
      new ValidatorCompanionDirector(self::$modelFactory),
      $target
    );

    $dir = new DirectoryIterator($modelDir->getPath());
    foreach ($dir AS $file) {
      if ($file->isDot() || $file->isDir()) {
        continue;
      }

      $filename = $file->getFilename();
      if (substr($filename, -4) != '.php') {
        continue;
      }

      $className = $modelDir->getPrefix() . substr($filename, 0, -4);
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
    $modelFactory = new ModelFactory($annotationFactory);

    self::$annoFactory = $annotationFactory;
    self::$modelFactory = $modelFactory;
  }
}
