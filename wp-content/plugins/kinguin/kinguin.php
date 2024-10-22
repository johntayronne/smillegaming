<?php
/**
    Plugin Name: Kinguin API for WooCommerce
    Description: Import over 70,000 digital products to your online store, including video games, software, gift cards and in-game content.
    Product: Kinguin
    Version: 1.0.7
	Author: iLabs.dev
	Author URI: https://ilabs.dev/
	Text Domain: kinguin
	Domain Path: /lang/

	@package \WPDesk\ILKinguin

	Copyright 2022 Inspire Labs sp. z o.o.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version     = '1.0.7';

$plugin_name        = 'Kinguin';
$plugin_class_name  = '\WPDesk\ILKinguin\Plugin';
$plugin_text_domain = 'kinguin';
$product_id         = 'kinguin';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );
$kinguin_plugin_dir = dirname( __FILE__ );
define( 'KINGUIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$requirements = [
	'php'     => '7.0',
	'wp'      => '5.0',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '4.7',
		],
	],
];

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52-free.php';
