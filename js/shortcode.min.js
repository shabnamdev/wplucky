jQuery(window).on('elementor/frontend/init', () => {
    "use strict";
    elementorFrontend.hooks.addAction('frontend/element_ready/wordpress-lucky-wheel.default', function ($scope) {
        if (!window.elementor) {
            return;
        }
        let $container = $scope.find('.wp-lucky-wheel-shortcode-container');
        if (!$container || $container.length === 0) {
            return;
        }
        let shortcode_id = $container.attr('id');
        let shortcode_args = $container.data('shortcode_args');
        let congratulations_effect = shortcode_args.congratulations_effect;
        let font_size = shortcode_args.font_size;
        let wheel_size = shortcode_args.wheel_size;
        let custom_field_name_enable = shortcode_args.custom_field_name_enable;
        let custom_field_name_required = shortcode_args.custom_field_name_required;
        let custom_field_mobile_enable = shortcode_args.custom_field_mobile_enable;
        let custom_field_mobile_required = shortcode_args.custom_field_mobile_required;
        let color = shortcode_args.color;
        let slices_text_color = shortcode_args.slices_text_color;
        let label = shortcode_args.label;
        let piece_coupons = shortcode_args.prize_type;
        let wplwl_center_color = shortcode_args.wheel_center_color;
        let wplwl_border_color = shortcode_args.wheel_border_color;
        let wplwl_dot_color = shortcode_args.wheel_dot_color;
        let gdpr_checkbox = shortcode_args.gdpr;
        let wplwl_spinning_time = shortcode_args.spinning_time;
        let wheel_speed = shortcode_args.wheel_speed;
        let center_image = shortcode_args.center_image;
        let wplwl_skip_enter_email = shortcode_args.wplwl_skip_enter_email;

        let slices = piece_coupons.length;
        let sliceDeg = 360 / slices;
        let deg = -(sliceDeg / 2);
        let cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1')[0];
        if (cv === undefined) {
            return;
        }
        let ctx = cv.getContext('2d');

        let canvas_width;
        let wd_width, wd_height;
        wd_width = window.innerWidth;
        wd_height = window.innerHeight;
        if (wd_width > wd_height) {
            canvas_width = wd_height;
        } else {
            canvas_width = wd_width;
        }
        let width = parseInt(canvas_width * 0.75 + 16);// size
        if (canvas_width > 480) {
            width = parseInt(wheel_size * (canvas_width * 0.55 + 16) / 100);
        }
        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = jQuery(cv).attr('width');
            let hidefCanvasHeight = jQuery(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;
            jQuery(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            jQuery(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            jQuery(cv).css('width', hidefCanvasCssWidth);
            jQuery(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }

        let center = width / 2; // center
        $container.find('.wp-lucky-wheel-shortcode-wheel-canvas').css({'width': width + 'px', 'height': width + 'px'});
        let inline_css = '';
        if (shortcode_args.pointer_position === 'center') {
            inline_css += '#' + shortcode_id + ' .wp-lucky-wheel-shortcode-wheel-pointer:before{font-size:' + parseInt(width / 4) + 'px !important; }';
        } else {
            inline_css += '#' + shortcode_id + ' .wp-lucky-wheel-shortcode-wheel-pointer:before{font-size:' + parseInt(width / 10) + 'px !important; }';
            if ($(window).width() < 640) {
                inline_css += '#' + shortcode_id + '.wp-lucky-wheel-shortcode-margin-position .wp-lucky-wheel-shortcode-wheel-container .wp-lucky-wheel-shortcode-wheel-pointer-container .wp-lucky-wheel-shortcode-wheel-pointer:after{width:' + parseInt(width / 25) + 'px !important;height:' + parseInt(width / 25) + 'px !important;bottom:' + parseInt(width / 50) + 'px !important; left:' + parseInt(width / 50) + 'px;}';
            } else {
                inline_css += '#' + shortcode_id + '.wp-lucky-wheel-shortcode-margin-position .wp-lucky-wheel-shortcode-wheel-container .wp-lucky-wheel-shortcode-wheel-pointer-container .wp-lucky-wheel-shortcode-wheel-pointer:after{width:' + parseInt(width / 25) + 'px !important;height:' + parseInt(width / 25) + 'px !important;bottom:' + parseInt(width / 25) + 'px !important; }';
            }
        }
        jQuery('head').append('<style type="text/css">' + inline_css + '</style>');
        let wheel_text_size;
        wheel_text_size = parseInt(width / 28) * parseInt(font_size) / 100;

        function wplwl_shortcode_deg2rad(deg) {
            return deg * Math.PI / 180;
        }

        function wplwl_shortcode_drawSlice(deg, color) {
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.moveTo(center, center);
            let r;
            if (width <= 480) {
                r = width / 2 - 10;
            } else {
                r = width / 2 - 14;
            }
            ctx.arc(center, center, r, wplwl_shortcode_deg2rad(deg), wplwl_shortcode_deg2rad(deg + sliceDeg));
            ctx.lineTo(center, center);
            ctx.fill();
        }

        function wplwl_shortcode_drawPoint(deg, color) {
            ctx.save();
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 8;
            ctx.shadowOffsetY = 8;
            ctx.shadowColor = 'rgba(0,0,0,0.2)';
            ctx.arc(center, center, width / 8, 0, 2 * Math.PI);
            ctx.fill();
            ctx.clip();
            ctx.restore();
        }

        function wplwl_shortcode_drawBorder(borderC, dotC, lineW, dotR, des, shadColor) {
            let center1 = center - 2 * des;
            ctx.beginPath();
            ctx.strokeStyle = borderC;
            ctx.lineWidth = lineW;
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 6;
            ctx.shadowOffsetY = 6;
            ctx.shadowColor = shadColor;
            ctx.arc(center, center, center1, 0, 2 * Math.PI);
            ctx.stroke();
            let x_val, y_val, deg;
            deg = sliceDeg / 2;
            for (let i = 0; i < slices; i++) {
                ctx.beginPath();
                ctx.fillStyle = dotC;
                x_val = center + center1 * Math.cos(deg * Math.PI / 180);
                y_val = center - center1 * Math.sin(deg * Math.PI / 180);
                ctx.arc(x_val, y_val, dotR, 0, 2 * Math.PI);
                ctx.fill();
                deg += sliceDeg;
            }
        }

        function wplwl_shortcode_drawText(deg, text, color) {
            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(wplwl_shortcode_deg2rad(deg));
            ctx.textAlign = "right";
            ctx.direction = 'rtl';
            ctx.fillStyle = color;
            ctx.font = '400 ' + wheel_text_size + 'px "shabnam", Tahoma, Arial, sans-serif';
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;
            text = text.replace(/&#(\d{1,4});/g, function (fullStr, code) {
                return String.fromCharCode(code);
            });
            let reText = text.split('\/n'), text1 = '', text2 = '';
            if (reText.length > 1) {
                text1 = reText[0];
                text2 = reText.splice(1, reText.length - 1);
                text2 = text2.join('');
            } else {
                reText = text.split('\\n');
                if (reText.length > 1) {
                    text1 = reText[0];
                    text2 = reText.splice(1, reText.length - 1);
                    text2 = text2.join('');
                }
            }
            if (text1.trim() !== "" && text2.trim() !== "") {
                ctx.fillText(text1.trim(), 7 * center / 8, -(wheel_text_size * 1 / 4));
                ctx.fillText(text2.trim(), 7 * center / 8, wheel_text_size * 3 / 4);
            } else {
                ctx.fillText(text.replace(/\\n/g, '').replace(/\/n/g, ''), 7 * center / 8, wheel_text_size / 2 - 2);
            }
            ctx.restore();
        }

        function wplwl_shortcode_spins_wheel($container, stop_position, result_notification, result) {
            let canvas_1 = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1');
            let canvas_3 = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-3');
            let default_css = '';
            if (window.devicePixelRatio) {
                default_css = 'width:' + width + 'px;height:' + width + 'px;';
            }
            canvas_1.attr('style', default_css);
            canvas_3.attr('style', default_css);
            let stop_deg = 360 - sliceDeg * stop_position;
            let wheel_stop = wheel_speed * 360 * wplwl_spinning_time + stop_deg;
            let css = default_css + '-moz-transform: rotate(' + wheel_stop + 'deg);-webkit-transform: rotate(' + wheel_stop + 'deg);-o-transform: rotate(' + wheel_stop + 'deg);-ms-transform: rotate(' + wheel_stop + 'deg);transform: rotate(' + wheel_stop + 'deg);';
            css += '-webkit-transition: transform ' + wplwl_spinning_time + 's ease;-moz-transition: transform ' + wplwl_spinning_time + 's ease;-ms-transition: transform ' + wplwl_spinning_time + 's ease;-o-transition: transform ' + wplwl_spinning_time + 's ease;transition: transform ' + wplwl_spinning_time + 's ease;';
            canvas_1.attr('style', css);
            canvas_3.attr('style', css);
            spinning = true;
            setTimeout(function () {
                if (result === 'win' && congratulations_effect === 'firework') {
                    $container.find('.wplwl-congratulations-effect').addClass('wplwl-congratulations-effect-firework');
                }
                $container.find('.wp-lucky-wheel-shortcode-wheel-fields-container').html('<div class="wp-lucky-wheel-shortcode--frontend-result">' + result_notification + '</div>');
                $container.find('.wp-lucky-wheel-shortcode-wheel-button-wrap').removeClass('wp-lucky-wheel-shortcode-loading');
                css = default_css + 'transform: rotate(' + stop_deg + 'deg);';
                canvas_1.attr('style', css);
                canvas_3.attr('style', css);
                spinning = false;
            }, parseInt(wplwl_spinning_time * 1000))
        }

        function isValidEmailAddress(emailAddress) {
            let pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}jQuery/i;
            return pattern.test(emailAddress);
        }

        let spinning = false;

        function wplwl_shortcode_check_email() {
            $container.find('.wp-lucky-wheel-shortcode-wheel-button-wrap').on('click', function () {
                if (spinning) {
                    return;
                }
                let $button = jQuery(this);
                let $error_email = $container.find('.wp-lucky-wheel-shortcode-wheel-field-error-email');
                $error_email.html('');
                let $email = $container.find('.wp-lucky-wheel-shortcode-wheel-field-email');
                let $email_container = $email.closest('.wp-lucky-wheel-shortcode-wheel-field-email-wrap');
                let $name = $container.find('.wp-lucky-wheel-shortcode-wheel-field-name');
                let $name_container = $name.closest('.wp-lucky-wheel-shortcode-wheel-field-name-wrap');
                let $mobile = $container.find('.wp-lucky-wheel-shortcode-wheel-field-mobile');
                let $mobile_container = $mobile.closest('.wp-lucky-wheel-shortcode-wheel-field-mobile-wrap');
                $email_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                $name_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                $mobile_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                if ('on' === gdpr_checkbox && !jQuery('.wp-lucky-wheel-shortcode-wheel-gdpr-wrap input[type="checkbox"]').prop('checked')) {
                    alert(shortcode_args.gdpr_warning);
                    return false;
                }
                let wplwl_email = $email.val();
                let wplwl_name = $name.val();
                let wplwl_mobile = $mobile.val();
                let qualified = true;
                let focus_field;
                if (!wplwl_skip_enter_email) {
                    if (!wplwl_email) {
                        $email.prop('disabled', false);
                        focus_field = $email;
                        $error_email.html(shortcode_args.empty_email_warning);
                        $email_container.addClass('wp-lucky-wheel-shortcode-required-field');
                        qualified = false;
                    }

                    if (!isValidEmailAddress(wplwl_email)) {
                        $email.prop('disabled', false);
                        focus_field = $email;
                        $error_email.html(shortcode_args.invalid_email_warning);
                        $email_container.addClass('wp-lucky-wheel-shortcode-required-field');
                        qualified = false;
                    }
                }
                if (custom_field_mobile_enable === 'on' && custom_field_mobile_required === 'on' && !wplwl_mobile) {
                    $mobile_container.addClass('wp-lucky-wheel-shortcode-required-field');
                    $mobile.attr('placeholder', shortcode_args.custom_field_mobile_message);
                    focus_field = $mobile;
                    qualified = false;
                }
                if (custom_field_name_enable === 'on' && custom_field_name_required === 'on' && !wplwl_name) {
                    $name_container.addClass('wp-lucky-wheel-shortcode-required-field');
                    $name.attr('placeholder', shortcode_args.custom_field_name_message);
                    focus_field = $name;
                    qualified = false;
                }

                if (qualified === false) {
                    focus_field.focus();
                    return false;
                }
                $email.prop('disabled', true);
                $error_email.html('');
                $button.addClass('wp-lucky-wheel-shortcode-loading');
                jQuery.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: shortcode_args.ajaxurl,
                    data: {
                        user_email: wplwl_email,
                        user_name: wplwl_name,
                        user_mobile: wplwl_mobile,
                        language: shortcode_args.language,
                        _wordpress_lucky_wheel_nonce: jQuery('#_wordpress_lucky_wheel_nonce').val(),
                    },
                    success: function (response) {
                        if (response.allow_spin === 'yes') {
                            wplwl_shortcode_spins_wheel($container, response.stop_position, response.result_notification, response.result);
                            if (response.url_redirect_after_spin) {
                                location.replace(response.url_redirect_after_spin);
                            }
                        } else {
                            $button.removeClass('wp-lucky-wheel-shortcode-loading');
                            $email.prop('disabled', false);
                            alert(response.allow_spin);
                        }
                    }
                });
            });
        }

        wplwl_shortcode_check_email();
        let center1 = 32;

        function wplwl_shortcode_render_base_wheel() {
            let original_cv = cv;
            let original_ctx = ctx;
            let render_cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1')[0];
            if (!render_cv) {
                return;
            }
            cv = render_cv;
            ctx = render_cv.getContext('2d');
            ctx.clearRect(0, 0, width, width);
            let render_deg = -(sliceDeg / 2);
            for (let i = 0; i < slices; i++) {
                wplwl_shortcode_drawSlice(render_deg, color[i]);
                wplwl_shortcode_drawText(render_deg + sliceDeg / 2, label[i], slices_text_color[i]);
                render_deg += sliceDeg;
            }
            cv = original_cv;
            ctx = original_ctx;
        }

        wplwl_shortcode_render_base_wheel();
        deg = 360 - sliceDeg / 2;
        if (document.fonts && document.fonts.load) {
            document.fonts.load('400 ' + Math.max(12, wheel_text_size) + 'px shabnam').then(function () {
                wplwl_shortcode_render_base_wheel();
            }).catch(function () {});
        }
        cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-2')[0];
        ctx = cv.getContext('2d');
        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = jQuery(cv).attr('width');
            let hidefCanvasHeight = jQuery(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;

            jQuery(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            jQuery(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            jQuery(cv).css('width', hidefCanvasCssWidth);
            jQuery(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        wplwl_shortcode_drawPoint(deg, wplwl_center_color);
        if (center_image) {
            let wl_image = new Image;
            wl_image.onload = function () {
                cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-2')[0];
                ctx = cv.getContext('2d');
                let image_size = 2 * (width / 8 - 7);
                ctx.arc(center, center, image_size / 2, 0, 2 * Math.PI);
                ctx.clip();
                ctx.drawImage(wl_image, center - image_size / 2, center - image_size / 2, image_size, image_size);

            };
            wl_image.src = center_image;
        }

        if (width <= 480) {
            wplwl_shortcode_drawBorder(wplwl_border_color, 'rgba(0,0,0,0)', 8, 3, 4, 'rgba(0,0,0,0.2)');
        } else {
            wplwl_shortcode_drawBorder(wplwl_border_color, 'rgba(0,0,0,0)', 16, 6, 7, 'rgba(0,0,0,0.2)');
        }
        cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-3')[0];
        ctx = cv.getContext('2d');

        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = jQuery(cv).attr('width');
            let hidefCanvasHeight = jQuery(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;

            jQuery(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            jQuery(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            jQuery(cv).css('width', hidefCanvasCssWidth);
            jQuery(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        if (width <= 480) {
            wplwl_shortcode_drawBorder('rgba(0,0,0,0)', wplwl_dot_color, 8, 3, 4, 'rgba(0,0,0,0)');
        } else {
            wplwl_shortcode_drawBorder('rgba(0,0,0,0)', wplwl_dot_color, 16, 6, 7, 'rgba(0,0,0,0)');
        }
    })
});
jQuery(document).ready(function ($) {
    "use strict";
    $('.wp-lucky-wheel-shortcode-container').map(function () {
        let $container = $(this);
        let shortcode_id = $container.attr('id');
        let shortcode_args = $container.data('shortcode_args');
        let congratulations_effect = shortcode_args.congratulations_effect;
        let font_size = shortcode_args.font_size;
        let wheel_size = shortcode_args.wheel_size;
        let custom_field_name_enable = shortcode_args.custom_field_name_enable;
        let custom_field_name_required = shortcode_args.custom_field_name_required;
        let custom_field_mobile_enable = shortcode_args.custom_field_mobile_enable;
        let custom_field_mobile_required = shortcode_args.custom_field_mobile_required;
        let color = shortcode_args.color;
        let slices_text_color = shortcode_args.slices_text_color;
        let label = shortcode_args.label;
        let piece_coupons = shortcode_args.prize_type;
        let wplwl_center_color = shortcode_args.wheel_center_color;
        let wplwl_border_color = shortcode_args.wheel_border_color;
        let wplwl_dot_color = shortcode_args.wheel_dot_color;
        let gdpr_checkbox = shortcode_args.gdpr;
        let wplwl_spinning_time = shortcode_args.spinning_time;
        let wheel_speed = shortcode_args.wheel_speed;
        let center_image = shortcode_args.center_image;
        let wplwl_skip_enter_email = shortcode_args.wplwl_skip_enter_email;

        let slices = piece_coupons.length;
        let sliceDeg = 360 / slices;
        let deg = -(sliceDeg / 2);
        let cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1')[0];
        if (cv === undefined) {
            return;
        }
        let ctx = cv.getContext('2d');

        let canvas_width;
        let wd_width, wd_height;
        wd_width = window.innerWidth;
        wd_height = window.innerHeight;
        if (wd_width > wd_height) {
            canvas_width = wd_height;
        } else {
            canvas_width = wd_width;
        }
        let width = parseInt(canvas_width * 0.75 + 16);// size
        if (canvas_width > 480) {
            width = parseInt(wheel_size * (canvas_width * 0.55 + 16) / 100);
        }
        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = $(cv).attr('width');
            let hidefCanvasHeight = $(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;
            $(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            $(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            $(cv).css('width', hidefCanvasCssWidth);
            $(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }

        let center = width / 2; // center
        $container.find('.wp-lucky-wheel-shortcode-wheel-canvas').css({'width': width + 'px', 'height': width + 'px'});
        let inline_css = '';
        if (shortcode_args.pointer_position === 'center') {
            inline_css += '#' + shortcode_id + ' .wp-lucky-wheel-shortcode-wheel-pointer:before{font-size:' + parseInt(width / 4) + 'px !important; }';
        } else {
            inline_css += '#' + shortcode_id + ' .wp-lucky-wheel-shortcode-wheel-pointer:before{font-size:' + parseInt(width / 10) + 'px !important; }';
            if ($(window).width() < 640) {
                inline_css += '#' + shortcode_id + '.wp-lucky-wheel-shortcode-margin-position .wp-lucky-wheel-shortcode-wheel-container .wp-lucky-wheel-shortcode-wheel-pointer-container .wp-lucky-wheel-shortcode-wheel-pointer:after{width:' + parseInt(width / 25) + 'px !important;height:' + parseInt(width / 25) + 'px !important;bottom:' + parseInt(width / 50) + 'px !important; left:' + parseInt(width / 50) + 'px;}';
            } else {
                inline_css += '#' + shortcode_id + '.wp-lucky-wheel-shortcode-margin-position .wp-lucky-wheel-shortcode-wheel-container .wp-lucky-wheel-shortcode-wheel-pointer-container .wp-lucky-wheel-shortcode-wheel-pointer:after{width:' + parseInt(width / 25) + 'px !important;height:' + parseInt(width / 25) + 'px !important;bottom:' + parseInt(width / 25) + 'px !important; }';
            }
        }
        $('head').append('<style type="text/css">' + inline_css + '</style>');
        let wheel_text_size;
        wheel_text_size = parseInt(width / 28) * parseInt(font_size) / 100;

        function wplwl_shortcode_deg2rad(deg) {
            return deg * Math.PI / 180;
        }

        function wplwl_shortcode_drawSlice(deg, color) {
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.moveTo(center, center);
            let r;
            if (width <= 480) {
                r = width / 2 - 10;
            } else {
                r = width / 2 - 14;
            }
            ctx.arc(center, center, r, wplwl_shortcode_deg2rad(deg), wplwl_shortcode_deg2rad(deg + sliceDeg));
            ctx.lineTo(center, center);
            ctx.fill();
        }

        function wplwl_shortcode_drawPoint(deg, color) {
            ctx.save();
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 8;
            ctx.shadowOffsetY = 8;
            ctx.shadowColor = 'rgba(0,0,0,0.2)';
            ctx.arc(center, center, width / 8, 0, 2 * Math.PI);
            ctx.fill();
            ctx.clip();
            ctx.restore();
        }

        function wplwl_shortcode_drawBorder(borderC, dotC, lineW, dotR, des, shadColor) {
            let center1 = center - 2 * des;
            ctx.beginPath();
            ctx.strokeStyle = borderC;
            ctx.lineWidth = lineW;
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 6;
            ctx.shadowOffsetY = 6;
            ctx.shadowColor = shadColor;
            ctx.arc(center, center, center1, 0, 2 * Math.PI);
            ctx.stroke();
            let x_val, y_val, deg;
            deg = sliceDeg / 2;
            for (let i = 0; i < slices; i++) {
                ctx.beginPath();
                ctx.fillStyle = dotC;
                x_val = center + center1 * Math.cos(deg * Math.PI / 180);
                y_val = center - center1 * Math.sin(deg * Math.PI / 180);
                ctx.arc(x_val, y_val, dotR, 0, 2 * Math.PI);
                ctx.fill();
                deg += sliceDeg;
            }
        }

        function wplwl_shortcode_drawText(deg, text, color) {
            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(wplwl_shortcode_deg2rad(deg));
            ctx.textAlign = "right";
            ctx.direction = 'rtl';
            ctx.fillStyle = color;
            ctx.font = '400 ' + wheel_text_size + 'px "shabnam", Tahoma, Arial, sans-serif';
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;
            text = text.replace(/&#(\d{1,4});/g, function (fullStr, code) {
                return String.fromCharCode(code);
            });
            let reText = text.split('\/n'), text1 = '', text2 = '';
            if (reText.length > 1) {
                text1 = reText[0];
                text2 = reText.splice(1, reText.length - 1);
                text2 = text2.join('');
            } else {
                reText = text.split('\\n');
                if (reText.length > 1) {
                    text1 = reText[0];
                    text2 = reText.splice(1, reText.length - 1);
                    text2 = text2.join('');
                }
            }
            if (text1.trim() !== "" && text2.trim() !== "") {
                ctx.fillText(text1.trim(), 7 * center / 8, -(wheel_text_size * 1 / 4));
                ctx.fillText(text2.trim(), 7 * center / 8, wheel_text_size * 3 / 4);
            } else {
                ctx.fillText(text.replace(/\\n/g, '').replace(/\/n/g, ''), 7 * center / 8, wheel_text_size / 2 - 2);
            }
            ctx.restore();
        }

        function wplwl_shortcode_spins_wheel($container, stop_position, result_notification, result) {
            let canvas_1 = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1');
            let canvas_3 = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-3');
            let default_css = '';
            if (window.devicePixelRatio) {
                default_css = 'width:' + width + 'px;height:' + width + 'px;';
            }
            canvas_1.attr('style', default_css);
            canvas_3.attr('style', default_css);
            let stop_deg = 360 - sliceDeg * stop_position;
            let wheel_stop = wheel_speed * 360 * wplwl_spinning_time + stop_deg;
            let css = default_css + '-moz-transform: rotate(' + wheel_stop + 'deg);-webkit-transform: rotate(' + wheel_stop + 'deg);-o-transform: rotate(' + wheel_stop + 'deg);-ms-transform: rotate(' + wheel_stop + 'deg);transform: rotate(' + wheel_stop + 'deg);';
            css += '-webkit-transition: transform ' + wplwl_spinning_time + 's ease;-moz-transition: transform ' + wplwl_spinning_time + 's ease;-ms-transition: transform ' + wplwl_spinning_time + 's ease;-o-transition: transform ' + wplwl_spinning_time + 's ease;transition: transform ' + wplwl_spinning_time + 's ease;';
            canvas_1.attr('style', css);
            canvas_3.attr('style', css);
            spinning = true;
            setTimeout(function () {
                if (result === 'win' && congratulations_effect === 'firework') {
                    $container.find('.wplwl-congratulations-effect').addClass('wplwl-congratulations-effect-firework');
                }
                $container.find('.wp-lucky-wheel-shortcode-wheel-fields-container').html('<div class="wp-lucky-wheel-shortcode--frontend-result">' + result_notification + '</div>');
                $container.find('.wp-lucky-wheel-shortcode-wheel-button-wrap').removeClass('wp-lucky-wheel-shortcode-loading');
                css = default_css + 'transform: rotate(' + stop_deg + 'deg);';
                canvas_1.attr('style', css);
                canvas_3.attr('style', css);
                spinning = false;
            }, parseInt(wplwl_spinning_time * 1000))
        }

        function isValidEmailAddress(emailAddress) {
            let pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/i;
            return pattern.test(emailAddress);
        }

        let spinning = false;

        function validateShortcodeRecaptcha(response) {
            if (response) {
                jQuery('.wplwl-shortcode-recaptcha-field #wplwl-shortcode-g-validate-response').val(response);
            }
        }

        function expireRecaptcha() {
            jQuery('.wplwl-shortcode-recaptcha-field #wplwl-shortcode-g-validate-response').val(null);

        }

        /* Googele recaptcha */
        window.addEventListener('load', function () {
            if (shortcode_args.wplwl_recaptcha == 'on') {
                if (shortcode_args.wplwl_recaptcha_version == 2) {
                    wplwlShortcodeReCaptchaV2Onload();
                } else {
                    wplwlShortcodeReCaptchaV3Onload();
                    jQuery('.wplwl-shortcode-recaptcha-field').hide();
                }
            } else {
                jQuery('.wplwl-shortcode-recaptcha-field').hide();
            }
        });

        function wplwlShortcodeReCaptchaV3Onload() {
            grecaptcha.ready(function () {
                grecaptcha.execute(shortcode_args.wplwl_recaptcha_site_key, {action: 'homepage'}).then(function (token) {
                    validateShortcodeRecaptcha(token);
                })
            });
        }

        function wplwlShortcodeReCaptchaV2Onload() {
            if (jQuery.find('.wplwl-shortcode-recaptcha').length == 0 || jQuery.find('.wplwl-shortcode-recaptcha iframe').length) {
                return true;
            }
            grecaptcha.render('wplwl-shortcode-recaptcha', {
                'sitekey': shortcode_args.wplwl_recaptcha_site_key,
                'callback': validateShortcodeRecaptcha,
                'expired-callback': expireRecaptcha,
                'theme': shortcode_args.wplwl_recaptcha_secret_theme,
                'isolated': false
            });
        }

        function wplwl_shortcode_check_email() {
            /*reCaptcha*/
            $('.wp-lucky-wheel-shortcode-content-container').on('renderReCaptcha', function () {
                if (shortcode_args.wplwl_recaptcha == 'on') {
                    if (shortcode_args.wplwl_recaptcha_version == 2) {
                        wplwlShortcodeReCaptchaV2Onload();
                    } else {
                        wplwlShortcodeReCaptchaV3Onload();
                        $('.wp-lucky-wheel-shortcode-content-container').find('.wplwl-shortcode-recaptcha-field').hide();
                    }
                }
            });

            window.addEventListener('load', function () {
                if (shortcode_args.wplwl_recaptcha == 'on') {
                    if (shortcode_args.wplwl_recaptcha_version == 2) {
                        wplwlShortcodeReCaptchaV2Onload();
                    } else {
                        wplwlShortcodeReCaptchaV3Onload();
                        $('.wp-lucky-wheel-shortcode-content-container').find('.wplwl-shortcode-recaptcha-field').hide();
                    }
                }
            });
            $container.find('.wp-lucky-wheel-shortcode-wheel-button-wrap').on('click', function () {
                if (spinning) {
                    return;
                }
                let $button = $(this);
                let $error_email = $container.find('.wp-lucky-wheel-shortcode-wheel-field-error-email');
                $error_email.html('');
                let $email = $container.find('.wp-lucky-wheel-shortcode-wheel-field-email');
                let $email_container = $email.closest('.wp-lucky-wheel-shortcode-wheel-field-email-wrap');
                let $name = $container.find('.wp-lucky-wheel-shortcode-wheel-field-name');
                let $name_container = $name.closest('.wp-lucky-wheel-shortcode-wheel-field-name-wrap');
                let $mobile = $container.find('.wp-lucky-wheel-shortcode-wheel-field-mobile');
                let $mobile_container = $mobile.closest('.wp-lucky-wheel-shortcode-wheel-field-mobile-wrap');
                $email_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                $name_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                $mobile_container.removeClass('wp-lucky-wheel-shortcode-required-field');
                if ('on' === gdpr_checkbox && !$('.wp-lucky-wheel-shortcode-wheel-gdpr-wrap input[type="checkbox"]').prop('checked')) {
                    alert(shortcode_args.gdpr_warning);
                    return false;
                }
                let wplwl_email = $email.val();
                let wplwl_name = $name.val();
                let wplwl_mobile = $mobile.val();
                let shortcode_g_validate_response = $('#wplwl-shortcode-g-validate-response').val();
                let qualified = true;
                let focus_field;
                if (!wplwl_skip_enter_email) {

                    if (!wplwl_email) {
                        $email.prop('disabled', false);
                        focus_field = $email;
                        $error_email.html(shortcode_args.empty_email_warning);
                        $email_container.addClass('wp-lucky-wheel-shortcode-required-field');
                        qualified = false;
                    }

                    if (!isValidEmailAddress(wplwl_email)) {
                        $email.prop('disabled', false);
                        focus_field = $email;
                        $error_email.html(shortcode_args.invalid_email_warning);
                        $email_container.addClass('wp-lucky-wheel-shortcode-required-field');
                        qualified = false;
                    }
                }
                if (shortcode_args.wplwl_recaptcha && shortcode_args.wplwl_recaptcha_site_key && !shortcode_g_validate_response) {
                    $('.wplwl-shortcode-recaptcha-field #wplwl-shortcode-recaptcha > div').addClass('wplwl-required-field').focus();
                    $('#wplwl_warring_recaptcha').html(shortcode_args.wplwl_warring_recaptcha);
                    qualified = false;
                } else if (shortcode_args.wplwl_recaptcha && shortcode_args.wplwl_recaptcha_site_key) {
                    $('.wplwl-shortcode-recaptcha-field #wplwl-shortcode-recaptcha > div').removeClass('wplwl-required-field');
                    qualified = true;
                }
                if (custom_field_mobile_enable === 'on' && custom_field_mobile_required === 'on' && !wplwl_mobile) {
                    $mobile_container.addClass('wp-lucky-wheel-shortcode-required-field');
                    $mobile.attr('placeholder', shortcode_args.custom_field_mobile_message);
                    focus_field = $mobile;
                    qualified = false;
                }
                if (custom_field_name_enable === 'on' && custom_field_name_required === 'on' && !wplwl_name) {
                    $name_container.addClass('wp-lucky-wheel-shortcode-required-field');
                    $name.attr('placeholder', shortcode_args.custom_field_name_message);
                    focus_field = $name;
                    qualified = false;
                }

                if (qualified === false) {
                    focus_field.focus();
                    return false;
                }
                $email.prop('disabled', true);
                $error_email.html('');
                $button.addClass('wp-lucky-wheel-shortcode-loading');
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: shortcode_args.ajaxurl,
                    data: {
                        user_email: wplwl_email,
                        user_name: wplwl_name,
                        user_mobile: wplwl_mobile,
                        language: shortcode_args.language,
                        g_validate_response: shortcode_g_validate_response,
                        _wordpress_lucky_wheel_nonce: $('#_wordpress_lucky_wheel_nonce').val(),
                    },
                    success: function (response) {
                        if (response.allow_spin === 'yes') {
                            wplwl_shortcode_spins_wheel($container, response.stop_position, response.result_notification, response.result);
                        } else {
                            $button.removeClass('wp-lucky-wheel-shortcode-loading');
                            $email.prop('disabled', false);
                            if (response.g_validate_response) {
                                alert(response.warning);
                            } else {
                                alert(response.allow_spin);
                            }
                        }
                    }
                });
            });
        }

        wplwl_shortcode_check_email();
        let center1 = 32;

        function wplwl_shortcode_render_base_wheel() {
            let original_cv = cv;
            let original_ctx = ctx;
            let render_cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-1')[0];
            if (!render_cv) {
                return;
            }
            cv = render_cv;
            ctx = render_cv.getContext('2d');
            ctx.clearRect(0, 0, width, width);
            let render_deg = -(sliceDeg / 2);
            for (let i = 0; i < slices; i++) {
                wplwl_shortcode_drawSlice(render_deg, color[i]);
                wplwl_shortcode_drawText(render_deg + sliceDeg / 2, label[i], slices_text_color[i]);
                render_deg += sliceDeg;
            }
            cv = original_cv;
            ctx = original_ctx;
        }

        wplwl_shortcode_render_base_wheel();
        deg = 360 - sliceDeg / 2;
        if (document.fonts && document.fonts.load) {
            document.fonts.load('400 ' + Math.max(12, wheel_text_size) + 'px shabnam').then(function () {
                wplwl_shortcode_render_base_wheel();
            }).catch(function () {});
        }
        cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-2')[0];
        ctx = cv.getContext('2d');
        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = $(cv).attr('width');
            let hidefCanvasHeight = $(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;

            $(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            $(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            $(cv).css('width', hidefCanvasCssWidth);
            $(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        wplwl_shortcode_drawPoint(deg, wplwl_center_color);
        if (center_image) {
            let wl_image = new Image;
            wl_image.onload = function () {
                cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-2')[0];
                ctx = cv.getContext('2d');
                let image_size = 2 * (width / 8 - 7);
                ctx.arc(center, center, image_size / 2, 0, 2 * Math.PI);
                ctx.clip();
                ctx.drawImage(wl_image, center - image_size / 2, center - image_size / 2, image_size, image_size);

            };
            wl_image.src = center_image;
        }

        if (width <= 480) {
            wplwl_shortcode_drawBorder(wplwl_border_color, 'rgba(0,0,0,0)', 8, 3, 4, 'rgba(0,0,0,0.2)');
        } else {
            wplwl_shortcode_drawBorder(wplwl_border_color, 'rgba(0,0,0,0)', 16, 6, 7, 'rgba(0,0,0,0.2)');
        }
        cv = $container.find('.wp-lucky-wheel-shortcode-wheel-canvas-3')[0];
        ctx = cv.getContext('2d');

        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            let hidefCanvasWidth = $(cv).attr('width');
            let hidefCanvasHeight = $(cv).attr('height');
            let hidefCanvasCssWidth = hidefCanvasWidth;
            let hidefCanvasCssHeight = hidefCanvasHeight;

            $(cv).attr('width', hidefCanvasWidth * window.devicePixelRatio);
            $(cv).attr('height', hidefCanvasHeight * window.devicePixelRatio);
            $(cv).css('width', hidefCanvasCssWidth);
            $(cv).css('height', hidefCanvasCssHeight);
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        if (width <= 480) {
            wplwl_shortcode_drawBorder('rgba(0,0,0,0)', wplwl_dot_color, 8, 3, 4, 'rgba(0,0,0,0)');
        } else {
            wplwl_shortcode_drawBorder('rgba(0,0,0,0)', wplwl_dot_color, 16, 6, 7, 'rgba(0,0,0,0)');
        }
    })
});
