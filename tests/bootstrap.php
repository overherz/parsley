<?php
/*
 * This file is part of the Parsley package.
 *
 * (c) 2013 Bogdan Padalko <zaq178miami@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$loader->add('Parsley\Tests', __DIR__);

//$loader->add('Parsley\Helpers', __DIR__);
