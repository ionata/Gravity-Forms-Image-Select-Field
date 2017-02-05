<?php

class GF_Field_Image_Select extends GF_Field {
    public $type = 'image_select';
    const PREFIX = 'data:application/json;base64,';

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
            'text'  => $this->get_form_editor_field_title()
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
            'default_value_setting',
            'description_setting',
            'file_extensions_setting',
            'label_placement_setting',
            'label_setting',
            'prepopulate_field_setting',
            'size_setting',

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

    public function get_form_inline_script_on_page_render( $form ) {

        $id = absint( $this->id );

        add_filter( 'media_view_strings', array( &$this, 'custom_media_uploader' ) );
        wp_enqueue_media();

        $ver = '1.0.0';
        $min = '.min';
        // $min = '';
        wp_enqueue_script( 'cropperjs', plugins_url( "cropperjs/cropper$min.js", __FILE__ ), $deps = array(), $ver, $in_footer = true );
        wp_enqueue_style( 'cropperjs', plugins_url( "cropperjs/cropper$min.css", __FILE__ ), $deps = array(), $ver );

        $ver = GF_IMAGE_SELECT_VERSION;
        // $ver = rand( 10000, 20000 );
        wp_enqueue_script( 'gf_image_select', plugins_url( 'image-select.js', __FILE__ ), $deps = array( 'jquery', 'cropperjs' ), $ver, $in_footer = true);
        wp_enqueue_style( 'gf_image_select', plugins_url( 'image-select.css', __FILE__ ), $deps = array(), $ver );

        return ";new GFImageSelect({ key: $id });";

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

        $form_id = absint( $form['id'] );
        $id      = absint( $this->id );

        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();
        $is_admin        = $is_entry_detail || $is_form_editor;

        $size         = $this->size;
        $class_suffix = $is_entry_detail ? '_admin' : '';
        $class        = $size . $class_suffix;

        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

        $extensions_message = '';

        $allowed_extensions = ! empty( $this->allowedExtensions ) ? join( ',', GFCommon::clean_extensions( explode( ',', strtolower( $this->allowedExtensions ) ) ) ) : array();
        if ( ! empty( $allowed_extensions ) ) {
            $extensions_message = esc_attr( sprintf( __( 'Accepted file types: %s.', 'gravityforms' ), str_replace( ',', ', ', $allowed_extensions ) ) );
        }

        if ( $is_entry_detail ) {
            $input = "<input type='hidden' id='input_{$id}' name='input_{$id}' value='{$value}' />";

            return $input . '<br/>' . esc_html__( 'Image selecton is not editable', GF_IMAGE_SELECT_DOMAIN );
        }

        $src = '';
        $width = '100';
        $height = '100';
        $has_src = '';
        if ( ! empty( $value ) ) {
            if ( $this->is_decodable( $value ) ) {
                $data = $this->decode( $value );
                $attachment_id = $data->attachmentId;
            } else {
                $attachment_id = $value;
            }

            if ( $attachment_id ) {
                $image = wp_get_attachment_image_src( $attachment_id, 'large' );
                if ( $image ) {
                    $src = $image[0];
                    $width = $image[1];
                    $height = $image[2];
                    $has_src = 'gf_image_select_has_src';
                }
            }
        }

        // $logic_event = $this->get_conditional_logic_event( 'change' );

        // $tabindex1 = $this->get_tabindex();
        // $tabindex2 = $this->get_tabindex();

        $non_admin_html = '';
        if ( ! $is_admin ) {
            $non_admin_html .= "<span id='extensions_message' class='screen-reader-text'>{$extensions_message}</span>"; // NOTE(evo): screen reader only
            $non_admin_html .= "<div class='validation_message'></div>";
        }

        $string_button = esc_attr__( 'Select image', GF_IMAGE_SELECT_DOMAIN );
        $string_choose = esc_attr__( 'Select an image', GF_IMAGE_SELECT_DOMAIN );
        $string_update = esc_attr__( 'Use this image', GF_IMAGE_SELECT_DOMAIN );

        $string_crop = esc_attr__( 'Preview', GF_IMAGE_SELECT_DOMAIN );
        $string_back = esc_attr__( 'Back', GF_IMAGE_SELECT_DOMAIN );

        $input = <<<TEMPLATE
<div class='ginput_container ginput_container_image_select $class $has_src' id='gf_image_select_container_$id'>
    <input type='hidden' name='input_$id' id='gf_image_select_$id' value='$value' />

    <div class='gf_image_select_crop_wrapper'>
        <img id='gf_image_select_crop_$id' src='$src' width='$width' height='$height' class='gf_image_select_crop' />
    </div>

    <div id='gf_image_select_preview_$id' class='gf_image_select_preview'></div>

    <div class='gf_image_select_buttons'>
        <button
            type='button'
            id='gf_image_select_button_crop_$id'
            class='button gf_image_select_button gf_image_select_button_crop'
        >$string_crop</button>

        <button
            type='button'
            id='gf_image_select_button_back_$id'
            class='button gf_image_select_button gf_image_select_button_back'
        >$string_back</button>

        <button
            type='button'
            id='gf_image_select_button_$id'
            class='button gf_image_select_button gf_image_select_button_select'
            data-choose='$string_choose'
            data-update='$string_update'
            $disabled_text
        >$string_button</button>
    </div>

    $non_admin_html
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

        if ( $this->is_decodable( $value ) ) {
            $data = $this->decode_value( $value );

            $attachment_id = isset( $data->attachmentId ) ? $data->attachmentId : null;
            if ( $attachment_id ) {
                $image = wp_get_attachment_image_src( $attachment_id, 'large' );
                if ( ! $image ) {
                    $message = __( 'Invalid attachment', GF_IMAGE_SELECT_DOMAIN );
                }
            } else {
                $message = __( 'Missing attachment', GF_IMAGE_SELECT_DOMAIN );
            }

            $crop = isset( $data->crop ) ? $data->crop : null;
            if ( $crop ) {
                if ( ! isset( $crop->x ) || ! isset( $crop->y ) || ! isset( $crop->width ) || ! isset( $crop->height ) ) {
                    $message = __( 'Ivalid crop region', GF_IMAGE_SELECT_DOMAIN );
                }
            } else {
                $message = __( 'Missing crop region', GF_IMAGE_SELECT_DOMAIN );
            }
        } else {
            $attachment_id = $value;

            $image = wp_get_attachment_image_src( $attachment_id, 'large' );
            if ( $image ) {
                $message = __( 'Missing crop region', GF_IMAGE_SELECT_DOMAIN );
            }
        }

        if ( ! empty( $message ) ) {
            $this->failed_validation = true;
            $this->validation_message = $message;
        }

    }

    /**
     * Return the formatted entry value.
     *
     * @param array $entry The entry currently being processed.
     * @param string $input_id The field or input ID.
     * @param bool|false $use_text
     * @param bool|false $is_csv
     * @return string
     */
    public function get_value_export( $entry, $input_id = '', $use_text = false, $is_csv = false ) {

        if ( empty( $input_id ) ) {
            $input_id = $this->id;
        }

        $value = rgar( $entry, $input_id );

        if ( ! empty( $value ) ) {
            $form = GFAPI::get_form( $entry['form_id'] );
            $product_info = GFCommon::get_product_fields( $form, $entry );

            if ( $this->is_decodable( $value ) ) {
                $data = $this->decode_value( $value );

                $attachment_id = isset( $data->attachmentId ) ? $data->attachmentId : null;
                $crop = isset( $data->crop ) ? $data->crop : null;
                $width = isset( $data->width ) ? $data->width : null;
                $height = isset( $data->height ) ? $data->height : null;

                if ( $attachment_id && $crop && $width && $height) {
                    $src = wp_get_attachment_image_src( $attachment_id, 'large' );

                    if ( $src ) {
                        $value = sprintf( '%s/%sx%s/%s,%s,%s,%s', $src[0], $width, $height, $crop->x, $crop->y, $crop->width, $crop->height);
                    }
                }
            }
        }

        return $value;

    }

    public function is_decodable ( $value ) {
        return substr( $value, 0, strlen( self::PREFIX ) ) === self::PREFIX;
    }

    public function decode_value ( $value ) {
        return json_decode( rawurldecode( base64_decode( substr( $value, strlen( self::PREFIX ) ) ) ) );
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
