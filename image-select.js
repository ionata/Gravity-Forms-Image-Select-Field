+(function (window, undefined) {
    window.GFImageSelect = GFImageSelect;

    function GFImageSelect (props) {
        var props = props || {};

        var key = props.key;
        if (!key) {
            throw new Error('GFImageSelect: No key');
        }

        var postId = props.postId;
        postId = isNaN(postId) ? 0 : Math.abs(postId);

        var jQuery = props.jQuery || window.jQuery;
        if (!jQuery) {
            throw new Error('GFImageSelect: No jQuery');
        }

        jQuery(document).ready(function () {
            attach({
                key: key,
                postId: postId,
                jQuery: jQuery
            });
        });
    }

    function attach (props) {
        var key = props.key;
        var postId = props.postId;
        var jQuery = props.jQuery;

        var frame;

        GFImageSelect._storeMediaPostId();

        jQuery('#gf_image_select_button_' + key).on('click', function (event) {
            event.preventDefault();

            var $el = jQuery(this);

            if (frame) {
               frame.uploader.uploader.param('post_id', postId);
               frame.open();
               return;
            }

            GFImageSelect._setMediaPostId(postId);

            frame = wp.media({
                title: $el.data('choose'),
                library: {
                    type: 'image'
                },
                button: {
                    text: $el.data('update'),
                    close: true
                },
                multiple: false
            });

            wp.media.frames.imageSelect = frame;

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first();
                onSelect({
                    attachment: attachment,
                    key: key,
                    jQuery: jQuery
                });
            });

            frame.on('close', function () {
                GFImageSelect._restoreMediaPostId();
            })

            frame.open();
        });

        if (jQuery('#gf_image_select_container_' + key).hasClass('gf_image_select_has_src')) {
            var value = getValue(key);

            initCropper({
                key: key,
                jQuery: jQuery,
                attachmentId: value.attachmentId,
                crop: value.crop,
                width: value.width,
                height: value.height
            });
        }
    };

    function getValue (key) {
        var value = {};
        var rawValue = jQuery('#gf_image_select_' + key).val();

        if (rawValue) {
            var prefix = 'data:application/json;base64,';

            if (rawValue.substr(0, prefix.length) === prefix) {
                value = JSON.parse(atou(rawValue.substr(prefix.length)));
            } else {
                value.attachmentId = isNaN(rawValue) ? 0 : parseInt(rawValue, 10);
            }
        }

        return value;
    }

    function onSelect (props) {
        var attachment = props.attachment.toJSON();
        var large = attachment.sizes && attachment.sizes.large || attachment;

        var key = props.key;
        var jQuery = props.jQuery;

        jQuery('#gf_image_select_container_' + key).addClass('gf_image_select_has_src');
        jQuery('#gf_image_select_crop_' + key).attr('src', large.url);
        jQuery('#gf_image_select_' + key).val(attachment.id);

        initCropper({
            key: key,
            jQuery: jQuery,
            attachmentId: attachment.id
        });
    }

    function initCropper (props) {
        var key = props.key;
        var jQuery = props.jQuery;
        var attachmentId = props.attachmentId;
        var crop = props.crop;
        var width = props.width;
        var height = props.height;

        var el = jQuery('#gf_image_select_crop_' + key).get(0);
        var cropper = new Cropper(el, {
            viewMode: 1,
            aspectRatio: NaN,
            // aspectRatio: 16 / 9,
            // guides: false,
            // background: false,
            // autoCrop: false,
            // autoCropArea: 0.8,
            movable: false,
            rotatable: false,
            scalable: false,
            zoomable: false,
            minCropBoxWidth: 50,
            minCropBoxHeight: 50,
            ready: function () {
                var imageData = cropper.getImageData();
                width = imageData.naturalWidth;
                height = imageData.naturalHeight;

                if (crop) {
                    cropper.setData(crop);
                } else {
                    updateValue();
                }
            },
            cropend: throttle(updateValue)
        });

        jQuery('#gf_image_select_button_crop_' + key).off('click').on('click', function (event) {
            jQuery('#gf_image_select_container_' + key).addClass('gf_image_select_has_crop');

            if (document.createElement('canvas').getContext) {
                var canvas = cropper.getCroppedCanvas();

                if (canvas) {
                    jQuery('#gf_image_select_preview_' + key).html(canvas);
                }
            }

            updateValue();
        });

        jQuery('#gf_image_select_button_back_' + key).off('click').on('click', function (event) {
            jQuery('#gf_image_select_container_' + key).removeClass('gf_image_select_has_crop');
            jQuery('#gf_image_select_preview_' + key).empty();
        });

        function updateValue () {
            var crop = cropper.getData();
            var value = {
                attachmentId: attachmentId,
                width: width,
                height: height,
                crop: crop
            };
            console.log('value:', value);

            jQuery('#gf_image_select_' + key).val('data:application/json;base64,' + utoa(JSON.stringify(value)));
        }
    }

    // ucs-2 string to base64 encoded ascii
    function utoa(str) {
        return window.btoa(encodeURIComponent(str));
    }

    // base64 encoded ascii to ucs-2 string
    function atou(str) {
        return decodeURIComponent(window.atob(str));
    }

    function throttle(fn, threshhold, scope) {
        threshhold || (threshhold = 250);
        var last;
        var deferTimer;
        return function () {
            var context = scope || this;
            var now = +new Date,
            args = arguments;
            if (last && now < last + threshhold) {
                clearTimeout(deferTimer);
                deferTimer = setTimeout(function () {
                    last = now;
                    fn.apply(context, args);
                }, threshhold);
            } else {
                last = now;
                fn.apply(context, args);
            }
        };
    }

    GFImageSelect.prototype.constructor = GFImageSelect;

    GFImageSelect._restoreMediaPostId = function () {
        wp.media.model.settings.post.id = GFImageSelect.__media_post_id;
    };

    GFImageSelect._storeMediaPostId = function () {
        if (GFImageSelect.__media_post_id === undefined) {
            GFImageSelect.__media_post_id = wp.media.model.settings.post.id || 0;
        }
    };

    GFImageSelect._setMediaPostId = function (postId) {
        GFImageSelect._storeMediaPostId();
        wp.media.model.settings.post.id = postId;
    };
})(window);
