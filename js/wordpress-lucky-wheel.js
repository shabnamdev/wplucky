(function ($) {
    'use strict';
    if (typeof _wplwl_get_email_params === "undefined" || !_wplwl_get_email_params?.prize_type){
        return;
    }
    let wheel_params = _wplwl_get_email_params;
    let wd_width= window.innerWidth, wd_height= window.innerHeight , width,center, cv, ctx;
    let is_mobile = wd_width < 760 , mobile_enable= wheel_params?.wplwl_mobile_enable;
    if (is_mobile && !mobile_enable){
        return;
    }
    let slices = wheel_params.prize_type.length;
    let sliceDeg = 360 / slices;
    let deg = -(sliceDeg / 2);
    $(window).on('resize', function () {
        wd_width= window.innerWidth;
        wd_height= window.innerHeight;
        is_mobile = wd_width < 760;
        if (mobile_enable || !is_mobile) {
            $(document.body).trigger('wplwl-render-popup-wheel');
        }else {
            $('.wplwl_lucky_wheel_wrap').removeClass('wplwl_lucky_wheel_active');
        }
    });
    $(window).on('load',function () {
        if (wheel_params.wplwl_recaptcha == 'on' && wheel_params.wplwl_recaptcha_site_key) {
            if (wheel_params.wplwl_recaptcha_version == 2) {
                wplwlreCaptchaV2Onload();
            } else {
                wplwlreCaptchaV3Onload();
                $('.wplwl-recaptcha-field').hide();
            }
        } else {
            $('.wplwl-recaptcha-field').hide();
        }
    });
    $(document).ready(function ($) {
        setTimeout(function () {
            $(document.body).trigger('wplwl-render-popup-wheel');
        }, 100);
        $(document.body).on('wplwl-render-popup-wheel', function () {
            if (getCookie('wplwl_cookie')){
                return;
            }
            $('.wplwl_lucky_wheel_wrap').addClass('wplwl_lucky_wheel_active');
            width = wd_width > wd_height ? wd_height : wd_width;
            width = is_mobile ? parseInt(width * 0.6 + 16) : parseInt(wheel_params.wheel_size * (width * 0.55 + 16) / 100);
            design_wheel_with_custom_width();
            if ($('.wplwl_lucky_wheel_content_rendered').length){
                drawWheel();
            }else {
                drawPopupIcon();
                switch (wheel_params.intent){
                    case 'popup_icon':
                        setTimeout(function () {
                            $('.wplwl_wheel_icon').addClass('wplwl_show');
                        }, wheel_params.show_wheel * 1000)
                        break;
                    case 'show_wheel':
                        setTimeout(function () {
                            $('.wplwl_wheel_icon').trigger('click');
                        }, wheel_params.show_wheel * 1000);
                        break;
                    case 'on_scroll':
                        $(document).on('scroll', function () {
                            if (!getCookie('wplwl_cookie') && (!is_mobile || mobile_enable)) {
                                let htmlHeight = $('html').prop('scrollHeight');
                                let scrollHeight = window.innerHeight;
                                let scrollTop = $('html').prop('scrollTop');
                                if (htmlHeight > 0) {
                                    let scrollRate = (scrollTop + scrollHeight) * 100 / (htmlHeight);
                                    if (scrollRate > wheel_params.scroll_amount) {
                                        $('.wplwl_wheel_icon').trigger('click');
                                    }
                                }
                            }
                        });
                        break;
                    case 'on_exit':
                        $(window).on('mousemove', function (event) {
                            let scrollTop = $('html').prop('scrollTop');
                            let pageY = event.pageY;
                            if (!getCookie('wplwl_cookie') && (!is_mobile || mobile_enable)) {
                                if (pageY - scrollTop < 15) {
                                    $('.wplwl_wheel_icon').trigger('click');
                                }
                            }
                        })
                        break;
                }
            }
        });
        $(document).on('click','.wordpress-lucky-wheel-popup-icon',function (){
            if (!$('.wplwl_lucky_wheel_wrap.wplwl_lucky_wheel_content_rendered').length){
                $('.wplwl_lucky_wheel_wrap').addClass('wplwl_lucky_wheel_content_rendered');
                drawWheel(true);
                return;
            }
            if (!$('.wplwl_lucky_wheel_wrap').hasClass('wplwl_lucky_wheel_active')){
                return;
            }
            $('.wplwl_wheel_icon').removeClass('wplwl_show');
            $('.wplwl-overlay').show();
            $('html').addClass('wplwl-html');
            $('.wplwl_lucky_wheel_content').addClass('lucky_wheel_content_show');
        });
        $(document).on('click','.wplwl-close-wheel , .wplwl-close, .wplwl-overlay',function (){
            $('html').removeClass('wplwl-html');
            $('.wplwl-overlay').hide();
            setCookie('wplwl_cookie', 'closed', wheel_params.time_if_close);
            $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
            if (! wheel_params.hide_popup ) {
                $('.wplwl_wheel_icon').addClass('wplwl_show');
            }
        });
        $(document).on('click','.wplwl-never-again span',function (){
            setCookie('wplwl_cookie', 'never_show_again', 30 * 24 * 60 * 60);
            $('.wplwl_wheel_icon').addClass('wplwl_show');
            $('.wplwl-overlay').hide();
            $('html').removeClass('wplwl-html');
            $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
        });
        $(document).on('click','.wplwl-reminder-later-a',function (){
            setCookie('wplwl_cookie', 'reminder_later', 24 * 60 * 60);
            $('.wplwl_wheel_icon').addClass('wplwl_show');
            $('.wplwl-overlay').hide();
            $('html').removeClass('wplwl-html');
            $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
        });
        $(document).on('click','.wplwl-hide-after-spin',function (){
            $('.wplwl-overlay').hide();
            $('html').removeClass('wplwl-html');
            $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
            $('.wplwl_wheel_spin').css({'margin-left': '0', 'transition': '2s'});
        });
        $(document).on('renderReCaptcha','.wheel-content-wrapper',function (){
            if (wheel_params.wplwl_recaptcha == 'on') {
                if (wheel_params.wplwl_recaptcha_version == 2) {
                    wplwlreCaptchaV2Onload();
                } else {
                    wplwlreCaptchaV3Onload();
                    $('.wheel-content-wrapper').find('.wplwl-recaptcha-field').hide();
                }
            }
        });
        $(document).on('keypress', function (e) {
            if ($('.wplwl_lucky_wheel_content').hasClass('lucky_wheel_content_show') && e.keyCode === 13) {
                $('#wplwl_chek_mail').trigger('click');
            }
        });
        $(document).on('click','#wplwl_chek_mail',function (){
            if (!$('.wplwl_lucky_wheel_wrap').hasClass('wplwl_lucky_wheel_active')){
                return;
            }
            $('#wplwl_error_mail,#wplwl_error_name,#wplwl_error_mobile,#wplwl_warring_recaptcha').html('');
            $('.wplwl-required-field').removeClass('wplwl-required-field');
            if (wheel_params.gdpr && !$('.wplwl-gdpr-checkbox-wrap input[type="checkbox"]').prop('checked')) {
                $('#wplwl_error_mail').html(wheel_params.gdpr_warning);
                return false;
            }
            let wplwl_email = $('#wplwl_player_mail').val();
            let wplwl_name = $('#wplwl_player_name').val();
            let wplwl_mobile = formatPhoneNumber( $('#wplwl_player_mobile').val() || '' );
            let g_validate_response = $('#wplwl-g-validate-response').val() || '';
            let qualified = true;
            if (wheel_params.wplwl_recaptcha && wheel_params.wplwl_recaptcha_site_key && !g_validate_response) {
                $('.wplwl-recaptcha-field #wplwl-recaptcha > div').addClass('wplwl-required-field').focus();
                $('#wplwl_warring_recaptcha').html(wheel_params.wplwl_warring_recaptcha);
                qualified = false;
            } else if (wheel_params.wplwl_recaptcha && wheel_params.wplwl_recaptcha_site_key) {
                $('.wplwl-recaptcha-field #wplwl-recaptcha > div').removeClass('wplwl-required-field');
                qualified = true;
            }

            if (wheel_params.custom_field_name_enable && (!is_mobile || wheel_params.custom_field_name_enable_mobile) && wheel_params.custom_field_name_required  && !wplwl_name) {
                $('#wplwl_error_name').html(wheel_params.custom_field_name_message);
                $('.wplwl_field_name').addClass('wplwl-required-field');
                qualified = false;
            }
            if (wheel_params.custom_field_mobile_enable && (!is_mobile || wheel_params.custom_field_mobile_enable_mobile)) {
                if ( wheel_params.custom_field_mobile_required == 'on' && !wplwl_mobile ) {
                    $('#wplwl_error_mobile').html(wheel_params.custom_field_mobile_message);
                    $('.wplwl_field_mobile').addClass('wplwl-required-field');
                    qualified = false;
                }

                if ( ! isValidPhone( wplwl_mobile ) ) {
                    $('#wplwl_error_mobile').html(wheel_params.custom_field_mobile_warning);
                    $('.wplwl_field_mobile').addClass('wplwl-required-field');
                    qualified = false;
                }
                if (wplwl_mobile){
                    wplwl_mobile = ($('#wplwl_country_code').val() || '' ) + wplwl_mobile;
                }
            }
            /*
            * Check email when hook wplwl_skip_enter_email = false
            * */
            if (!wheel_params.wplwl_skip_enter_email) {
                if (!wplwl_email) {
                    $('#wplwl_player_mail').prop('disabled', false).focus();
                    $('#wplwl_error_mail').html(wheel_params.empty_email_warning);
                    $('.wplwl_field_email').addClass('wplwl-required-field');
                    qualified = false;
                }
            }
            if (qualified === false) {
                return false;
            }
            $(this).unbind();
            $('.wplwl-overlay').unbind();
            $('#wplwl_player_mail').prop('disabled', true);
            if (getCookie('wplwl_cookie') === "" || getCookie('wplwl_cookie') === 'closed') {
                if (wheel_params.wplwl_skip_enter_email || isValidEmailAddress(wplwl_email) ) {
                    $('#wplwl_chek_mail').addClass('wplwl-adding');
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        url: wheel_params.ajaxurl,
                        data: {
                            user_email: wplwl_email,
                            user_name: wplwl_name,
                            user_mobile: wplwl_mobile,
                            is_desktop: !is_mobile ? 1: '',
                            language: wheel_params.language,
                            g_validate_response: g_validate_response,
                            _wordpress_lucky_wheel_nonce: $('#_wordpress_lucky_wheel_nonce').val(),
                        },
                        success: function (response) {
                            if (response.allow_spin === 'yes') {
                                $('.wplwl-show-again-option').hide();
                                $('.wplwl-close-wheel').hide();
                                $('.wplwl-hide-after-spin').show();
                                spins_wheel(response.stop_position, response.result_notification, response.result);
                                let wplwl_show_again = _wplwl_get_email_params.show_again;
                                let wplwl_show_again_unit = _wplwl_get_email_params.show_again_unit;
                                switch (wplwl_show_again_unit) {
                                    case 'm':
                                        wplwl_show_again *= 60;
                                        break;
                                    case 'h':
                                        wplwl_show_again *= 60 * 60;
                                        break;
                                    case 'd':
                                        wplwl_show_again *= 60 * 60 * 24;
                                        break;
                                    default:
                                }
                                setCookie('wplwl_cookie', wplwl_email, wplwl_show_again);
                                if (response?.url_redirect_after_spin) {
                                    location.replace(response.url_redirect_after_spin);
                                }
                            } else {
                                $('#wplwl_chek_mail').removeClass('wplwl-adding');
                                $('#wplwl_player_mail').prop('disabled', false);
                                if (response.g_validate_response) {
                                    $('#wplwl_error_mail').html(response.warning);
                                } else {
                                    $('#wplwl_error_mail').html(response.allow_spin);
                                }
                            }
                            if (response.hasOwnProperty('metrilo_response')) {
                                let metrilo_response = response.metrilo_response;
                                if (metrilo_response.status === 'success' && window.hasOwnProperty('metrilo')) {
                                    if (window.metrilo.hasOwnProperty('customEvent') && typeof window.metrilo.customEvent === 'function') {
                                        window.metrilo.customEvent('wordpress_lucky_wheel');
                                    }
                                }
                            }

                        }
                    });

                } else {
                    $('#wplwl_player_mail').prop('disabled', false).focus();
                    $('#wplwl_error_mail').html(wheel_params.invalid_email_warning);
                    $('.wplwl_field_email').addClass('wplwl-required-field');
                }
            } else {
                $('#wplwl_error_mail').html(wheel_params.limit_time_warning);
                $('#wplwl_player_mail').prop('disabled', false);
            }
        });
    });
    /*falling leaves effect*/
    let wplwlLeafScene = function (el) {
        this.viewport = el;
        this.world = document.createElement('div');
        this.leaves = [];

        this.options = {
            numLeaves: 30,
            wind: {
                magnitude: 1.2,
                maxSpeed: 3,
                duration: 500,
                start: 0,
                speed: 0
            }
        };

        this.width = this.viewport.offsetWidth;
        this.height = this.viewport.offsetHeight;

        // animation helper
        this.timer = 0;

        this._resetLeaf = function (leaf) {

            // place leaf towards the top left
            leaf.x = this.width * 2 - Math.random() * this.width * 1.75;
            leaf.y = -10;
            leaf.z = Math.random() * 200;
            if (leaf.x > this.width) {
                leaf.x = this.width + 10;
                leaf.y = Math.random() * this.height / 2;
            }
            // at the start, the leaf can be anywhere
            if (this.timer == 0) {
                leaf.y = Math.random() * this.height;
            }

            // Choose axis of rotation.
            // If axis is not X, chose a random static x-rotation for greater letiability
            leaf.rotation.speed = Math.random() * 10;
            let randomAxis = Math.random();
            if (randomAxis > 0.5) {
                leaf.rotation.axis = 'X';
            } else if (randomAxis > 0.25) {
                leaf.rotation.axis = 'Y';
                leaf.rotation.x = Math.random() * 180 + 90;
            } else {
                leaf.rotation.axis = 'Z';
                leaf.rotation.x = Math.random() * 360 - 180;
                // looks weird if the rotation is too fast around this axis
                leaf.rotation.speed = Math.random() * 3;
            }

            // random speed
            leaf.xSpeedVariation = Math.random() * 0.8 - 0.4;
            leaf.ySpeed = Math.random() + 1.5;

            return leaf;
        }

        this._updateLeaf = function (leaf) {
            let leafWindSpeed = this.options.wind.speed(this.timer - this.options.wind.start, leaf.y);

            let xSpeed = leafWindSpeed + leaf.xSpeedVariation;
            leaf.x -= xSpeed;
            leaf.y += leaf.ySpeed;
            leaf.rotation.value += leaf.rotation.speed;

            let t = 'translateX( ' + leaf.x + 'px ) translateY( ' + leaf.y + 'px ) translateZ( ' + leaf.z + 'px ) ';
            if (wheel_params.rotate) {
                t += ' rotate' + leaf.rotation.axis + '( ' + leaf.rotation.value + 'deg )';
                if (leaf.rotation.axis !== 'X') {
                    t += ' rotateX(' + leaf.rotation.x + 'deg)';
                }
            }
            leaf.el.style.webkitTransform = t;
            leaf.el.style.MozTransform = t;
            leaf.el.style.oTransform = t;
            leaf.el.style.transform = t;

            // reset if out of view
            if (leaf.x < -10 || leaf.y > this.height + 10) {
                this._resetLeaf(leaf);
            }
        }

        this._updateWind = function () {
            // wind follows a sine curve: asin(b*time + c) + a
            // where a = wind magnitude as a function of leaf position, b = wind.duration, c = offset
            // wind duration should be related to wind magnitude, e.g. higher windspeed means longer gust duration

            if (this.timer === 0 || this.timer > (this.options.wind.start + this.options.wind.duration)) {

                this.options.wind.magnitude = Math.random() * this.options.wind.maxSpeed;
                this.options.wind.duration = this.options.wind.magnitude * 50 + (Math.random() * 20 - 10);
                this.options.wind.start = this.timer;

                let screenHeight = this.height;

                this.options.wind.speed = function (t, y) {
                    // should go from full wind speed at the top, to 1/2 speed at the bottom, using leaf Y
                    let a = this.magnitude / 2 * (screenHeight - 2 * y / 3) / screenHeight;
                    return a * Math.sin(2 * Math.PI / this.duration * t + (3 * Math.PI / 2)) + a;
                }
            }
        }
        this.init = function () {

            for (let i = 0; i < this.options.numLeaves; i++) {
                let leaf = {
                    el: document.createElement('div'),
                    x: 0,
                    y: 0,
                    z: 0,
                    rotation: {
                        axis: 'X',
                        value: 0,
                        speed: 0,
                        x: 0
                    },
                    xSpeedVariation: 0,
                    ySpeed: 0,
                    path: {
                        type: 1,
                        start: 0,

                    },
                    image: 1
                };
                this._resetLeaf(leaf);
                this.leaves.push(leaf);
                this.world.appendChild(leaf.el);
            }

            this.world.className = 'wplwl-leaf-scene';
            this.viewport.appendChild(this.world);

            // set perspective
            this.world.style.webkitPerspective = "400px";
            this.world.style.MozPerspective = "400px";
            this.world.style.oPerspective = "400px";
            this.world.style.perspective = "400px";

            // reset window height/width on resize
            let self = this;
            window.onresize = function (event) {
                self.width = self.viewport.offsetWidth;
                self.height = self.viewport.offsetHeight;
            };
        };
        this.render = function () {
            this._updateWind();
            for (let i = 0; i < this.leaves.length; i++) {
                this._updateLeaf(this.leaves[i]);
            }

            this.timer++;

            requestAnimationFrame(this.render.bind(this));
        };
    };
    function setCookie(cname, cvalue, expire) {
        let d = new Date();
        d.setTime(d.getTime() + (expire * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    function design_wheel_with_custom_width(){
        if ($('.wplwl_lucky_wheel_content.wplwl-background-effect-falling-leaves:not(.wplwl_lucky_wheel_content_init)').length){
            let leafContainer = document.querySelector('.wplwl_lucky_wheel_content.wplwl-background-effect-falling-leaves');
            let leaves = new wplwlLeafScene(leafContainer);
            leaves.init();
            leaves.render();
        }
        $('.wplwl_wheel_spin').css({'width': width + 'px', 'height': width + 'px'});
        $('.wplwl_lucky_wheel_content').removeClass('wplwl_lucky_wheel_content_mobile lucky_wheel_content_tablet');
        if (!is_mobile) {
            if ('on' === wheel_params.show_full_wheel) {
                $('.wplwl_lucky_wheel_content').css({'max-width': (width + 600) + 'px'});
            } else {
                $('.wplwl_lucky_wheel_content').css({'max-width': (0.6 * width + 600) + 'px'});
            }
            if (wd_width < 1024){
                $('.wplwl_lucky_wheel_content').addClass('lucky_wheel_content_tablet');
            }
        }else {
            $('.wplwl_lucky_wheel_content').addClass('wplwl_lucky_wheel_content_mobile');
        }
        if ((is_mobile && !wheel_params.custom_field_name_enable_mobile) ){
            $('.wplwl_field_name_wrap').hide();
        }else {
            $('.wplwl_field_name_wrap').show();
        }
        if ((is_mobile && !wheel_params.custom_field_mobile_enable_mobile)){
            $('.wplwl_field_mobile_wrap').hide();
        }else {
            $('.wplwl_field_mobile_wrap').show();
        }
        let inline_css = '.wplwl_lucky_wheel_content:not(.wplwl_lucky_wheel_content_mobile) .wheel-content-wrapper .wheel_content_left{min-width:' + (width + 35) + 'px}';
        inline_css += '.wplwl_lucky_wheel_content.wplwl_lucky_wheel_content_mobile .wheel_description{min-height:' + $('.wheel_description').css('height') + '}';
        if (wheel_params.pointer_position === 'center') {
            inline_css += '.wplwl_pointer:before{font-size:' + parseInt(width / 4) + 'px !important; }';
        } else {
            inline_css += '.wplwl_pointer:before{font-size:' + parseInt(width / 10) + 'px !important; }';
            inline_css += '.wplwl_margin_position .wplwl_wheel_spin_container .wplwl_pointer_content .wplwl_pointer:after{width:' + parseInt(width / 25) + 'px;height:' + parseInt(width / 25) + 'px;bottom:' + parseInt(width / 30) + 'px; }';
        }
        if (!$('#wplwl_lucky_wheel_custom_inline_css').length){
            $('head').append('<style id="wplwl_lucky_wheel_custom_inline_css"></style>');
        }
        $('#wplwl_lucky_wheel_custom_inline_css').html(inline_css);
        if ($('#wplwl_center_image').val()) {
            let wl_image = new Image;
            wl_image.onload = function () {
                let cv = document.getElementById('wplwl_canvas1');
                let ctx = cv.getContext('2d');
                let image_size = 2 * (width / 8 - 7);
                ctx.arc(center, center, image_size / 2, 0, 2 * Math.PI);
                ctx.clip();
                ctx.drawImage(wl_image, center - image_size / 2, center - image_size / 2, image_size, image_size);

            };
            wl_image.src = $('#wplwl_center_image').val();
        }
    }
    async function drawWheel(show_popup = false){
        center = (width) / 2;
        await drawCanvas('wplwl_canvas');
        await drawCanvas('wplwl_canvas1');
        await drawCanvas('wplwl_canvas2');
        if (show_popup){
            $('.wordpress-lucky-wheel-popup-icon').trigger('click');
        }
    }
    async function drawCanvas(canvas_id){
        if (!canvas_id){
            return;
        }
        cv = document.getElementById(canvas_id);
        if (!cv){
            return;
        }
        ctx = cv.getContext('2d');
        cv.width = width;
        cv.height = width;
        if (window.devicePixelRatio) {
            $(cv).attr({
                'width': width * window.devicePixelRatio,
                'height': width * window.devicePixelRatio
            });
            $(cv).css({'width': width , 'height': width});
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        switch (canvas_id){
            case 'wplwl_canvas':
                for (let i = 0; i < slices; i++) {
                    drawSlice(deg, wheel_params.bg_color[i]);
                    drawText(deg + sliceDeg / 2, wheel_params.label[i], wheel_params.slices_text_color[i]);
                    deg += sliceDeg;
                }
                break;
            case 'wplwl_canvas1':
                drawPoint(deg, wheel_params.wheel_center_color);
                if (width <= 480) {
                    drawBorder(wheel_params.wheel_border_color, 'rgba(0,0,0,0)', 20, 4, 5, 'rgba(0,0,0,0.2)');
                } else {
                    drawBorder(wheel_params.wheel_border_color, 'rgba(0,0,0,0)', 30, 6, 7, 'rgba(0,0,0,0.2)');
                }
                break;
            case 'wplwl_canvas2':
                if (width <= 480) {
                    drawBorder('rgba(0,0,0,0)', wheel_params.wheel_dot_color, 20, 4, 5, 'rgba(0,0,0,0)');
                } else {
                    drawBorder('rgba(0,0,0,0)', wheel_params.wheel_dot_color, 30, 6, 7, 'rgba(0,0,0,0)');
                }
                break;
        }
    }
    function drawPopupIcon() {
        cv = document.getElementById('wplwl_popup_canvas');
        if (!cv) {
            return;
        }
        ctx = cv.getContext('2d');
        center = 32;
        for (let k = 0; k < slices; k++) {
            drawSlice(deg, wheel_params.bg_color[k]);
            deg += sliceDeg;
        }
        drawPopupIconPoint(wheel_params.wheel_center_color);
        drawBorder(wheel_params.wheel_border_color, wheel_params.wheel_dot_color, 4, 1, 0);
    }
    function drawPopupIconPoint(color) {
        ctx.save();
        ctx.beginPath();
        ctx.fillStyle = color;
        ctx.arc(center, center, 8, 0, 2 * Math.PI);
        ctx.fill();
        ctx.restore();
    }
    function drawPoint(deg, color) {
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
    function drawBorder(borderC, dotC, lineW, dotR, des, shadColor='') {
        ctx.beginPath();
        ctx.strokeStyle = borderC;
        ctx.lineWidth = lineW;
        if (shadColor) {
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 8;
            ctx.shadowOffsetY = 8;
            ctx.shadowColor = shadColor;
        }
        ctx.arc(center, center, center, 0, 2 * Math.PI);
        ctx.stroke();
        let x_val, y_val, deg;
        deg = sliceDeg / 2;
        let center1 = center - des;
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
    function drawText(deg, text, color) {
        let font_text_wheel = 'shabnam',
            wheel_text_size = parseInt(width / 28) * parseInt(wheel_params.font_size) / 100;
        if (typeof wheel_params.font_text_wheel !== 'undefined' && wheel_params.font_text_wheel !== '') {
            font_text_wheel = wheel_params.font_text_wheel;
        }
        ctx.save();
        ctx.translate(center, center);
        ctx.rotate(deg2rad(deg));
        ctx.textAlign = "right";
        ctx.direction = 'rtl';
        ctx.fillStyle = color;
        ctx.font = '400 ' + wheel_text_size + 'px ' + font_text_wheel;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        text = text.replace(/&#(\d{1,4});/g, function (fullStr, code) {
            return String.fromCharCode(code);
        });
        text = text.replace(/&nbsp;/g, ' ');
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
            // ctx.fillText(text.replace(/\\n/g, '').replace(/\/n/g, ''), 7 * center / 8, wheel_text_size / 2 - 2);
            text = text.replace(/\\n/g, '').replace(/\/n/g, '');
            let wrappedText = wrapText(ctx, text, 7 * center / 8, wheel_text_size, width <= 480 ? (width / 3 - 10):(width /3 - 14), wheel_text_size);
            for (let wrappedTextItem of wrappedText) {
                ctx.fillText(wrappedTextItem.text, wrappedTextItem.x, wrappedTextItem.y);
            }
        }
        ctx.restore();
    }
    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        let words = text.split(' ');
        let countWords = words.length ;
        let line = ''; // This will store the text of the current line
        let testLine = ''; // This will store the text when we add a word, to test if it's too long
        let lineArray = [], result = []; // This is an array of lines, which the function will return
        for (let i in words) {
            testLine += `${words[i]} `;
            let metrics = ctx.measureText(testLine);
            let testWidth = metrics.width;
            if (testWidth > maxWidth && i > 0) {
                // Then the line is finished, push the current line into "lineArray"
                lineArray.push({text:line,x:x,y:y});
                line = `${words[i]} `;
                testLine = `${words[i]} `;
            } else {
                // If the test line is still less than the max width, then add the word to the current line
                line += `${words[i]} `;
            }
            if( i == countWords - 1) {
                lineArray.push({text:line,x:x,y:y});
            }
        }
        let start_y = y / 2 - 2, countLine = lineArray.length;
        if (countLine > 4){
            start_y = -y ;
        }else if (countLine > 2){
            start_y = -(y * 1 / 2);
        } else if (countLine > 1){
            start_y = -(y * 1 / 4);
        }
        for (let i in lineArray) {
            let tmp = lineArray[i];
            if (i > 0){
                start_y += lineHeight ;
            }
            tmp['y'] = start_y;
            result.push(tmp);
        }
        return result;
    }
    function drawSlice(deg, color) {
        ctx.beginPath();
        ctx.fillStyle = color;
        ctx.moveTo(center, center);
        let r = center;
        if (center !== 32) {
            if (width <= 480) {
                r = width / 2 - 10;
            } else {
                r = width / 2 - 14;
            }
        }
        ctx.arc(center, center, r, deg2rad(deg), deg2rad(deg + sliceDeg));
        ctx.lineTo(center, center);
        ctx.fill();
    }
    function deg2rad(deg) {
        return deg * Math.PI / 180;
    }
    function wplwlreCaptchaV3Onload() {
        if ( typeof grecaptcha === "undefined") {
            return;
        }
        grecaptcha.ready(function () {
            grecaptcha.execute(wheel_params.wplwl_recaptcha_site_key, {action: 'homepage'}).then(function (token) {
                validateRecaptcha(token);
            })
        });
    }
    function wplwlreCaptchaV2Onload() {
        if (!$('.wplwl-recaptcha').length  || $('.wplwl-recaptcha iframe').length || typeof grecaptcha === "undefined") {
            return;
        }
        grecaptcha.render('wplwl-recaptcha', {
            'sitekey': wheel_params.wplwl_recaptcha_site_key,
            'callback': validateRecaptcha,
            'expired-callback': expireRecaptcha,
            'theme': wheel_params.wplwl_recaptcha_secret_theme,
            'isolated': false
        });
    }
    function validateRecaptcha(response) {
        if (response) {
            $('.wplwl-recaptcha-field #wplwl-g-validate-response').val(response);
        }
    }
    function expireRecaptcha() {
        $('.wplwl-recaptcha-field #wplwl-g-validate-response').val(null);
    }
    function formatPhoneNumber(phone) {
        if (phone) {
            phone = phone.replace(/\(|\)|-|\.|\s|\+/gi, '');
            let firstChar = phone.slice(0, 1);
            phone = parseInt(firstChar) === 0 ? phone.slice(1) : phone;
        }

        return phone;
    }
    function isValidEmailAddress(emailAddress) {
        let pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/i;
        return pattern.test(emailAddress);
    }
    function isValidPhone( phone ){
        if (phone === '' || wheel_params.custom_field_mobile_required === 'on') {
            return true;
        }
        return /^\d{8,15}$/.test(phone);
    }
    function spins_wheel(stop_position, result_notification, result) {
        let canvas_1 = $('#wplwl_canvas');
        let canvas_3 = $('#wplwl_canvas2');
        let default_css = '';
        if (window.devicePixelRatio) {
            default_css = 'width:' + width + 'px;height:' + width + 'px;';
        }
        canvas_1.attr('style', default_css);
        canvas_3.attr('style', default_css);
        let stop_deg = 360 - sliceDeg * stop_position;
        let wplwl_spinning_time = wheel_params.spinning_time;
        let wheel_stop = wheel_params.wheel_speed * 360 * wplwl_spinning_time + stop_deg;
        let css = default_css + '-moz-transform: rotate(' + wheel_stop + 'deg);-webkit-transform: rotate(' + wheel_stop + 'deg);-o-transform: rotate(' + wheel_stop + 'deg);-ms-transform: rotate(' + wheel_stop + 'deg);transform: rotate(' + wheel_stop + 'deg);';
        css += '-webkit-transition: transform ' + wplwl_spinning_time + 's ease;-moz-transition: transform ' + wplwl_spinning_time + 's ease;-ms-transition: transform ' + wplwl_spinning_time + 's ease;-o-transition: transform ' + wplwl_spinning_time + 's ease;transition: transform ' + wplwl_spinning_time + 's ease;';
        canvas_1.attr('style', css);
        canvas_3.attr('style', css);
        setTimeout(function () {
            css = default_css + 'transform: rotate(' + stop_deg + 'deg);';
            canvas_1.attr('style', css);
            canvas_3.attr('style', css);

            $('.wplwl_lucky_wheel_content').addClass('wplwl-finish-spinning');
            $('.wplwl-overlay').unbind();
            $('.wplwl-overlay').on('click', function () {
                $('html').removeClass('wplwl-html');
                $(this).hide();

                $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
                $('.wplwl_wheel_spin').css({'margin-left': '0', 'transition': '2s'});
            });
            if (result === 'win' && wheel_params.congratulations_effect === 'firework') {
                $('.wplwl-congratulations-effect').addClass('wplwl-congratulations-effect-firework');
            }
            $('.wplwl_user_lucky').html('<div class="wplwl-frontend-result">' + result_notification + '</div>');
            $('.wplwl_user_lucky').fadeIn(300);
            let wplwl_auto_close = parseInt(wheel_params.auto_close);
            if (wplwl_auto_close > 0) {
                setTimeout(function () {
                    $('.wplwl-overlay').hide();
                    $('html').removeClass('wplwl-html');
                    $('.wplwl_lucky_wheel_content').removeClass('lucky_wheel_content_show');
                    $('.wplwl_wheel_spin').css({'margin-left': '0', 'transition': '2s'});
                }, wplwl_auto_close * 1000);
            }
        }, parseInt(wplwl_spinning_time * 1000))
    }
}(jQuery));
