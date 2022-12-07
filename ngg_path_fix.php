<?php
/**
 * @package NextGEN Gallery Path Fixer
 * @version 1.0.0
 */
/*
Plugin Name: NGG Path Fixer
Plugin URI:
Description: Re-links the paths of broken NextGEN Galleries.
Author: Mark Pemburn
Version: 1.0.0
Author URI:
*/
namespace Ngg_Path_Fix;

require_once __DIR__ . '/vendor/autoload.php';

use Ngg_Path_Fix\AdminPage;
//use Ngg_Path_Fix\FixPaths;

AdminPage::boot();


