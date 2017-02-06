<?php
/*
Plugin Name: Image Select for Gravity Forms
Plugin URI: https://ionata.com.au
Description: A custom field for Gravity Forms
Version: 0.1.0
Author: Evo Stamatov
Author URI: https://ionata.com.au

------------------------------------------------------------------------
Copyright 2017 Ionata Digital Ltd.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_IMAGE_SELECT_VERSION', '0.1.0' );
define( 'GF_IMAGE_SELECT_DOMAIN', 'gfimageselectdomain' );

add_action( 'gform_loaded', array( 'GFImageSelect_Bootstrap', 'load' ), 5 );

class GFImageSelect_Bootstrap {

    public static function load() {

        if ( ! class_exists( 'GFForms' ) ) {
            return;
        }

        require_once( 'class-image-select-field.php' );

        GF_Fields::register( new GFImageSelectField() );
    }

}
