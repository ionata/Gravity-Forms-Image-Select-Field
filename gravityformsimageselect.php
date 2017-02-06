<?php
/*
Plugin Name: Image Select for Gravity Forms
Plugin URI: https://ionata.com.au
Description: A custom field for Gravity Forms
Version: 0.1.0
Author: Evo Stamatov
Author URI: https://ionata.com.au

------------------------------------------------------------------------
MIT License

Copyright (c) 2017 Ionata Digital

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
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
