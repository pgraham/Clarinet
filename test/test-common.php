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
 * This file sets up the environment for running tests.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

$loader = require __DIR__ . '/../vendor/autoload.php';

// Register an autoloader for test classes. This will be used to load the
// mocks
$loader->add('zpt\orm\test', __DIR__);

// Register autoloaders for source classes
$ormPath = realpath(__DIR__ . '/..');
$loader->add('zeptech\orm', $ormPath);
$loader->add('zpt\orm', $ormPath);

// Register autoloader for dynamic classes
$loader->add('zpt\dyn\orm', __DIR__ . '/zpt/orm/test/mock/gen');
