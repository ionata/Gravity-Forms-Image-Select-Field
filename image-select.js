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

        var data = getData(key, jQuery);
        if (data && data.attachmentId && data.attachmentSrc) {
            initCropper({
                key: key,
                jQuery: jQuery,
                // width: data.width,
                // height: data.height,
                crop: data.crop
            });
        }
    };

    function getData (key, jQuery) {
        var prefix = '#gf_image_select_' + key;

        var attachmentId = jQuery(prefix + '_1').val() || void 0;
        if (attachmentId) {
            attachmentId = isNaN(attachmentId) ? 0 : parseInt(attachmentId, 10)
        }

        var attachmentSrc = jQuery(prefix + '_2').val() || void 0;

        var width = jQuery(prefix + '_3').val() || void 0;
        if (width) {
            width = isNaN(width) ? void 0 : parseInt(width, 10)
        }

        var height = jQuery(prefix + '_4').val() || void 0;
        if (height) {
            height = isNaN(height) ? void 0 : parseInt(height, 10)
        }

        var crop = jQuery(prefix + '_5').val() || void 0;
        if (crop) {
            var arr = JSON.parse(crop);

            crop = {
                x: parseFloat(arr[0]),
                y: parseFloat(arr[1]),
                width: parseFloat(arr[2]),
                height: parseFloat(arr[3]),
            }
        }

        return {
            attachmentId: attachmentId,
            attachmentSrc: attachmentSrc,
            width: width,
            height: height,
            crop: crop
        }
    }

    function updateData (data, key, jQuery) {
        var prefix = '#gf_image_select_' + key;

        var map = {
            attachmentId: prefix + '_1',
            attachmentSrc: prefix + '_2',
            width: prefix + '_3',
            height: prefix + '_4',
            crop: prefix + '_5'
        }

        for (var i in data) {
            if (map[i]) {
                jQuery(map[i]).val(data[i]);
            }
        }
    }

    function onSelect (props) {
        var attachment = props.attachment.toJSON();
        var large = attachment.sizes && attachment.sizes.large || attachment;

        var key = props.key;
        var jQuery = props.jQuery;

        updateData({
            attachmentId: attachment.id,
            attachmentSrc: large.url,
            // width: large.width,
            // height: large.height
        }, key, jQuery)

        jQuery('#gf_image_select_container_' + key).addClass('gf_image_select_has_src');
        jQuery('#gf_image_select_crop_' + key).attr('src', large.url);

        initCropper({
            key: key,
            jQuery: jQuery,
            // width: large.width,
            // height: large.height
        });
    }

    function initCropper (props) {
        var key = props.key;
        var jQuery = props.jQuery;
        var crop = props.crop;

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

                updateData({
                    width: width,
                    height: height
                }, key, jQuery)

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
            var data = cropper.getData();
            var crop = JSON.stringify([data.x, data.y, data.width, data.height]);

            updateData({
                crop: crop
            }, key, jQuery)
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
