<?php

/*
 * PHP QR Code encoder
 *
 * Common constants
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
namespace QRCode\Library;

// Encoding modes

define('QR_MODE_NUL', -1);
define('QR_MODE_NUM', 0);
define('QR_MODE_AN', 1);
define('QR_MODE_8', 2);
define('QR_MODE_KANJI', 3);
define('QR_MODE_STRUCTURE', 4);

// Levels of error correction.

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

// Supported output formats

define('QR_FORMAT_TEXT', 0);
define('QR_FORMAT_PNG', 1);

define('QR_CACHEABLE', true); // use cache - more disk reads but less CPU power, masks and format templates are stored there
define('QR_CACHE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR); // used when QR_CACHEABLE === true
define('QR_LOG_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR); // default error logs dir   

define('QR_FIND_BEST_MASK', true); // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
define('QR_FIND_FROM_RANDOM', false); // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
define('QR_DEFAULT_MASK', 2); // when QR_FIND_BEST_MASK === false

define('QR_PNG_MAXIMUM_SIZE', 1024); // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images

define('Spec_VERSION_MAX', 40);
define('Spec_WIDTH_MAX',   177);

define('QRCAP_WIDTH',        0);
define('QRCAP_WORDS',        1);
define('QRCAP_REMINDER',     2);
define('QRCAP_EC',           3);

define('STRUCTURE_HEADER_BITS', 20);
define('MAX_STRUCTURED_SYMBOLS', 16);

define('N1', 3);
define('N2', 3);
define('N3', 40);
define('N4', 10);

define('QR_IMAGE', true);

Tools::markTime('start');

/*
$QR_BASEDIR = dirname(__FILE__) . DIRECTORY_SEPARATOR;

// Required libs

include $QR_BASEDIR . "qrconst.php";
include $QR_BASEDIR . "qrconfig.php";
include $QR_BASEDIR . "Tools.php";
include $QR_BASEDIR . "Spec.php";
include $QR_BASEDIR . "Image.php";
include $QR_BASEDIR . "Input.php";
include $QR_BASEDIR . "Bitstream.php";
include $QR_BASEDIR . "Split.php";
include $QR_BASEDIR . "qrrscode.php";
include $QR_BASEDIR . "Mask.php";
include $QR_BASEDIR . "Encode.php";
*/