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

$loader = require_once __DIR__ . '/../vendor/autoload.php';
function getComposerLoader() {
	global $loader;
	return $loader;
}

// Register an autoloader for test classes. This will be used to load the
// mocks
$loader->addPsr4('zpt\\orm\\test\\', __DIR__ . '/common');

// Register autoloader for dynamic classes
$dynTarget = new zpt\opal\Psr4Dir(__DIR__ . '/gen', 'dyn\\');
$dynTarget->registerWith($loader);
