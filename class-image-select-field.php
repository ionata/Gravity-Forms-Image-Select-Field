<?php
/*
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

class GFImageSelectField extends GF_Field {
    /**
     * Sets the field type.
     *
     * @var string The type of field.
     */
    public $type = 'image_select';

    public function __construct( $data = array() ) {
        parent::__construct( $data );

        // TODO(evo): work out if the only way to manage inputs is this action ???
        add_action( 'gform_editor_js_set_default_values', array( &$this, 'set_default_values' ) );
        // $this->set_inputs();
    }

    //*
    function set_default_values() { ?>

        case 'image_select' :
            if (!field.label) {
                field.label = <?php echo json_encode( esc_html__( 'Image Select', GF_IMAGE_SELECT_DOMAIN ) ); ?>;
            }

            var attachmentId = new Input(field.id + '.1', <?php echo json_encode( gf_apply_filters( array( 'gf_image_select_attachment_id', rgget( 'id' ) ), esc_html__( 'ID', GF_IMAGE_SELECT_DOMAIN ), rgget( 'id' ) ) ) ?>);
            var attachmentSrc = new Input(field.id + '.2', <?php echo json_encode( gf_apply_filters( array( 'gf_image_select_attachment_id', rgget( 'id' ) ), esc_html__( 'Source', GF_IMAGE_SELECT_DOMAIN ) ) ) ?>);
            var width = new Input(field.id + '.3', <?php echo json_encode( gf_apply_filters( array( 'gf_image_select_attachment_id', rgget( 'id' ) ), esc_html__( 'Width', GF_IMAGE_SELECT_DOMAIN ) ) ) ?>);
            var height = new Input(field.id + '.4', <?php echo json_encode( gf_apply_filters( array( 'gf_image_select_attachment_id', rgget( 'id' ) ), esc_html__( 'Height', GF_IMAGE_SELECT_DOMAIN ) ) ) ?>);
            var crop = new Input(field.id + '.5', <?php echo json_encode( gf_apply_filters( array( 'gf_image_select_attachment_id', rgget( 'id' ) ), esc_html__( 'Crop', GF_IMAGE_SELECT_DOMAIN ) ) ) ?>);

            field.inputs = [attachmentId, attachmentSrc, width, height, crop];
            break;

      <?php

    }
    /**/

    /*
    private function set_inputs() {

        if ( $this->id ) {
            $inputs = array(
                array(
                    'id' => $this->id . '.1',
                    'label' => __( 'ID', GF_IMAGE_SELECT_DOMAIN )
                ),
                array(
                    'id' => $this->id . '.2',
                    'label' => __( 'Source', GF_IMAGE_SELECT_DOMAIN )
                ),
                array(
                    'id' => $this->id . '.3',
                    'label' => __( 'Width', GF_IMAGE_SELECT_DOMAIN )
                ),
                array(
                    'id' => $this->id . '.4',
                    'label' => __( 'Height', GF_IMAGE_SELECT_DOMAIN )
                ),
                array(
                    'id' => $this->id . '.5',
                    'label' => __( 'Crop', GF_IMAGE_SELECT_DOMAIN )
                ),
            );
            $this->inputs = $inputs;
        }

    }
    /**/

    /**
     * Return the field title, for use in the form editor.
     *
     * @return string
     */
    public function get_form_editor_field_title() {

        return esc_attr__( 'Image Select', GF_IMAGE_SELECT_DOMAIN );

    }

    /**
     * Assign the Coupon button to the Pricing Fields group.
     *
     * @return array
     */
    public function get_form_editor_button() {

        return array(
            'group' => 'advanced_fields',
            'text'  => $this->get_form_editor_field_title(),
        );

    }

    /**
     * Return the settings which should be available on the field in the form editor.
     *
     * @return array
     */
    function get_form_editor_field_settings() {

        return array(
            'css_class_setting',
            'description_setting',
            // 'file_extensions_setting',
            'label_placement_setting',
            'label_setting',
            'prepopulate_field_setting',
            // 'size_setting',

            // 'default_value_setting',
            // 'conditional_logic_field_setting',
            // 'error_message_setting',
            // 'admin_label_setting',
            // 'rules_setting',
            // 'visibility_setting',
            // 'duplicate_setting',
            // 'placeholder_setting',
            // 'phone_format_setting',
            // 'next_button_setting',
            // 'previous_button_setting',
        );

    }

    /**
     * Outputs any inline scripts to be used when the page is rendered.
     *
     * @param array $form The Form Object.
     * @return string The inline scripts.
     */
    public function get_form_inline_script_on_page_render( $form ) {

        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();
        $is_admin        = $is_entry_detail || $is_form_editor;

        $form_id = absint( $form['id'] );
        $id = absint( $this->id );

        $field_id = $is_admin || $form_id == 0 ? "$id" : "{$form_id}_$id";

        add_filter( 'media_view_strings', array( &$this, 'custom_media_uploader' ) );
        wp_enqueue_media();

        $ver = '1.0.0';
        $min = '.min';
        // $min = '';
        wp_enqueue_script( 'cropperjs', plugins_url( "cropperjs/cropper$min.js", __FILE__ ), $deps = array(), $ver, $in_footer = true );
        wp_enqueue_style( 'cropperjs', plugins_url( "cropperjs/cropper$min.css", __FILE__ ), $deps = array(), $ver );

        $ver = GF_IMAGE_SELECT_VERSION;
        // $ver = rand( 10000, 20000 );
        wp_enqueue_script( 'gf_image_select', plugins_url( 'image-select.js', __FILE__ ), $deps = array( 'jquery', 'cropperjs' ), $ver, $in_footer = true );
        wp_enqueue_style( 'gf_image_select', plugins_url( 'image-select.css', __FILE__ ), $deps = array(), $ver );

        return ";new GFImageSelect({ key: '$field_id' });";

    }

    /**
     * Returns the field inner markup.
     *
     * @param array        $form  The Form Object currently being processed.
     * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
     * @param null|array   $entry Null or the Entry Object currently being edited.
     * @return string The field input HTML markup.
     */
    public function get_field_input( $form, $value = '', $entry = null ) {

        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();
        $is_admin        = $is_entry_detail || $is_form_editor;

        $form_id = absint( $form['id'] );
        $id      = absint( $this->id );

        $field_id = $is_admin || $form_id == 0 ? "$id" : "{$form_id}_$id";
        $form_id  = $is_admin && empty( $form_id ) ? rgget( 'id' ) : $form_id;

        $size         = $this->size;
        $class_suffix = $is_entry_detail ? '_admin' : '';
        $class        = $size . $class_suffix;

        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

        $extensions_message = '';

        // $allowed_extensions = ! empty( $this->allowedExtensions ) ? join( ',', GFCommon::clean_extensions( explode( ',', strtolower( $this->allowedExtensions ) ) ) ) : array();
        // if ( ! empty( $allowed_extensions ) ) {
        //     $extensions_message = esc_attr( sprintf( __( 'Accepted file types: %s.', 'gravityforms' ), str_replace( ',', ', ', $allowed_extensions ) ) );
        // }

        $attachment_id = '';
        $attachment_src = '';
        $width = '100';
        $height = '100';
        $crop = '';

        if ( is_array( $value ) ) {
            $attachment_id = esc_attr( GFForms::get( $this->id . '.1', $value ) );
            $attachment_src = esc_attr( GFForms::get( $this->id . '.2', $value ) );
            $width = esc_attr( GFForms::get( $this->id . '.3', $value ) );
            $height = esc_attr( GFForms::get( $this->id . '.4', $value ) );
            $crop = esc_attr( GFForms::get( $this->id . '.5', $value ) );
        }

        if ( $is_entry_detail ) {
            $input = '';
            $input .= "<input type='hidden' name='input_{$id}.1' value='{$attachment_id}' />";
            $input .= "<input type='hidden' name='input_{$id}.2' value='{$attachment_src}' />";
            $input .= "<input type='hidden' name='input_{$id}.3' value='{$width}' />";
            $input .= "<input type='hidden' name='input_{$id}.4' value='{$height}' />";
            $input .= "<input type='hidden' name='input_{$id}.5' value='{$crop}' />";

            return $input . '<br/>' . esc_html__( 'Image selecton is not editable', GF_IMAGE_SELECT_DOMAIN );
        }

        if ( $attachment_id ) {
            $image = wp_get_attachment_image_src( $attachment_id, 'large' );

            if ( $image ) {
                $attachment_src = $image[0];
                $width = $image[1];
                $height = $image[2];
            } else {
                $attachment_id = '';
                $attachment_src = '';
                $width = '100';
                $height = '100';
            }
        } else if ( ! $attachment_src ) {
            $width = '100';
            $height = '100';
        }

        $has_src = '';
        if ( $attachment_src ) {
            $has_src = 'gf_image_select_has_src';
            $img_src = $attachment_src;
        } else {
            $img_src = '//:0';
        }

        // $required_attribute = $this->isRequired ? 'aria-required="true"' : '';
        // $invalid_attribute = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

        // $logic_event = $this->get_conditional_logic_event( 'change' );

        // $tabindex1 = $this->get_tabindex();
        // $tabindex2 = $this->get_tabindex();

        $no_display_admin = $is_admin ? "style='display:none'" : '';

        $non_admin_html = '';
        if ( ! $is_admin ) {
            $non_admin_html .= "<span id='extensions_message' class='screen-reader-text'>{$extensions_message}</span>"; // NOTE(evo): screen reader only
        }

        $string_button = esc_attr__( 'Select image', GF_IMAGE_SELECT_DOMAIN );
        $string_choose = esc_attr__( 'Select an image', GF_IMAGE_SELECT_DOMAIN );
        $string_update = esc_attr__( 'Use this image', GF_IMAGE_SELECT_DOMAIN );

        $string_crop = esc_attr__( 'Preview', GF_IMAGE_SELECT_DOMAIN );
        $string_back = esc_attr__( 'Back', GF_IMAGE_SELECT_DOMAIN );

        // gfield_trigger_change
        $input = <<<TEMPLATE
<div class='ginput_complex$class_suffix ginput_container ginput_container_image_select $class $has_src' id='gf_image_select_container_{$field_id}'>
    $non_admin_html

    <input type='hidden' name='input_{$id}.1' id='gf_image_select_{$field_id}_1' value='$attachment_id' />
    <input type='hidden' name='input_{$id}.2' id='gf_image_select_{$field_id}_2' value='$attachment_src' />
    <input type='hidden' name='input_{$id}.3' id='gf_image_select_{$field_id}_3' value='$width' />
    <input type='hidden' name='input_{$id}.4' id='gf_image_select_{$field_id}_4' value='$height' />
    <input type='hidden' name='input_{$id}.5' id='gf_image_select_{$field_id}_5' value='$crop' />

    <div class='gf_image_select_crop_wrapper'>
        <img id='gf_image_select_crop_{$field_id}' src='$img_src' width='$width' height='$height' class='gf_image_select_crop' />
    </div>

    <div id='gf_image_select_preview_{$field_id}' class='gf_image_select_preview'></div>

    <div class='gf_image_select_buttons'>
        <button
            type='button'
            id='gf_image_select_button_crop_{$field_id}'
            class='button gf_image_select_button gf_image_select_button_crop'
            $disabled_text
            $no_display_admin
        >$string_crop</button>

        <button
            type='button'
            id='gf_image_select_button_back_{$field_id}'
            class='button gf_image_select_button gf_image_select_button_back'
            $disabled_text
            $no_display_admin
        >$string_back</button>

        <button
            type='button'
            id='gf_image_select_button_{$field_id}'
            class='button gf_image_select_button gf_image_select_button_select'
            data-choose='$string_choose'
            data-update='$string_update'
            $disabled_text
        >$string_button</button>
    </div>

    <div class='gf_clear gf_clear_complex'></div>
</div>
TEMPLATE;

        return $input;

    }

    /**
     * Re-validate the attachment id and crop region.
     *
     * @param string $value The field value from the $_POST
     * @param array $form The form object currently being processed.
     */
    public function validate( $value, $form ) {

        if ( empty( $value ) ) {
            return;
        }

        $message = '';

        if ( is_array( $value ) ) {
            $attachment_id  = trim( rgget( $this->id . '.1', $value ) );
            $attachment_src = trim( rgget( $this->id . '.2', $value ) );
            $width          = trim( rgget( $this->id . '.3', $value ) );
            $height         = trim( rgget( $this->id . '.4', $value ) );
            $crop           = trim( rgget( $this->id . '.5', $value ) );

            if ( $attachment_id ) {
                $image = wp_get_attachment_image_src( $attachment_id, 'large' );

                if ( ! $image || ! $attachment_src ) {
                    $message = __( 'Invalid attachment', GF_IMAGE_SELECT_DOMAIN );
                } else if ( $attachment_src !== $image[0] ) {
                    $message = __( 'Invalid source', GF_IMAGE_SELECT_DOMAIN );
                } else if ( ! $crop ) {
                    $message = __( 'Missing crop region', GF_IMAGE_SELECT_DOMAIN );
                } else {
                    $_crop = json_decode( $crop );
                    if ( ! isset( $_crop[0] ) || ! isset( $_crop[1] ) || ! isset( $_crop[2] ) || ! isset( $_crop[3] ) ) {
                        $message = __( 'Ivalid crop region', GF_IMAGE_SELECT_DOMAIN );
                    }
                }
            } else {
                $message = __( 'Missing attachment', GF_IMAGE_SELECT_DOMAIN );
            }
        }

        if ( ! empty( $message ) ) {
            $this->failed_validation = true;
            $this->validation_message = empty( $this->errorMessage ) ? $message : $this->errorMessage;
        }

    }

    /**
     * Gets the field value to be displayed on the entry detail page.
     *
     * @param array|string $value    The value of the field input.
     * @param string       $currency Not used.
     * @param bool         $use_text Not used.
     * @param string       $format   The format to output the value. Defaults to 'html'.
     * @param string       $media    Not used.
     * @return array|string The value to be displayed on the entry detail page.
     */
    public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

        if ( is_array( $value ) ) {
            $attachment_id  = trim( rgget( $this->id . '.1', $value ) );
            $attachment_src = trim( rgget( $this->id . '.2', $value ) );
            $width          = trim( rgget( $this->id . '.3', $value ) );
            $height         = trim( rgget( $this->id . '.4', $value ) );
            $crop           = trim( rgget( $this->id . '.5', $value ) );

            $image = null;
            $src_match_id = false;
            $ratio = 0;

            if ( $attachment_id ) {
                $image = wp_get_attachment_image_src( $attachment_id, 'large' );
                $src_match_id = $image && $attachment_src === $image[0];
                if ( $width > 0 ) {
                    $ratio = $image[1] / $width;
                }
            }

            if ( $format === 'html' ) {
                $return = '<div style="position:relative;">';

                if ( $attachment_id && $src_match_id ) {
                    $thumb = wp_get_attachment_image( $attachment_id, 'large' );
                    $return .= sprintf( '<a href="%s" target="_blank">%s</a>', $image[0], $thumb );
                } else if ( $attachment_src && ! $src_match_id ) {
                    $return .= sprintf( '<a href="%s" target="_blank">%s</a>', esc_attr( $attachment_src ), esc_html( $attachment_src ) );
                    if ( $width && $height ) {
                        $return .= sprintf( '<br/>Width: %s, Height: %s', esc_html( $width ), esc_html( $height ) );
                    }
                }

                if ( $crop ) {
                    list( $cx, $cy, $cw, $ch ) = json_decode( $crop );
                    if ( $ratio > 0 ) {
                        $return .= sprintf( '<div style="position:absolute;left:%0.3fpx;top:%0.3fpx;width:%0.3fpx;height:%0.3fpx;border:2px solid rgba(255,255,255,.7);box-shadow:0 2px 5px rgba(0,0,0,0.5);"></div>', $cx * $ratio, $cy * $ratio, $cw * $ratio, $ch * $ratio );
                    }
                    $return .= sprintf( '<br/>Crop: x: %0.3f, y: %0.3f, w: %0.3f, h: %0.3f', $cx, $cy, $cw, $ch );
                }

                $return .= sprintf( '<br/>Dimensions: %dx%d', $width, $height );

                $return .= '</div>';
            } else {
                $return = $attachment_src;
                if ( $crop ) {
                    list( $cx, $cy, $cw, $ch ) = json_decode( $crop );
                    $return .= "\n" . sprintf( '<br/>Crop: x: %0.3f, y: %0.3f, w: %0.3f, h: %0.3f', $cx, $cy, $cw, $ch );
                }
            }
        } else {
            $return = $value;
        }

        return $return;

    }

    /**
     * Configure the Media Picker
     *
     * @param  array $strings
     * @return array
     */
    public function custom_media_uploader( $strings ) {

        unset( $strings['selected'] ); //Removes Upload Files & Media Library links in Insert Media tab
        unset( $strings['insertMediaTitle'] ); //Insert Media
        unset( $strings['uploadFilesTitle'] ); //Upload Files
        // unset( $strings['mediaLibraryTitle'] ); //Media Library
        unset( $strings['createGalleryTitle'] ); //Create Gallery
        unset( $strings['setFeaturedImageTitle'] ); //Set Featured Image
        unset( $strings['insertFromUrlTitle'] ); //Insert from URL

        return $strings;

    }
}
