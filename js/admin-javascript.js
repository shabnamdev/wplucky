jQuery(document).ready(function ($) {
    'use strict';
    $(document).on('click','.wplw-migrate-to-new-table', function (){
        if (!confirm('Do you want to migrate to new table?')) {
            return;
        }
        let $thisBtn = $(this);
        $thisBtn.addClass('loading');
        $.ajax({
            url: wp_lucky_wheel_params_admin.url,
            type: 'post',
            data: {
                action: 'wplw_migrate_to_new_table',
                wplw_nonce: wp_lucky_wheel_params_admin.nonce,
            },
            success:function (res) {
                $thisBtn.text(res.data);
                $thisBtn.removeClass('wplw-migrate-to-new-table');
            },
            complete:function () {
                $thisBtn.removeClass('loading');
            }
        });
    });
    if ($('.vi-ui.tabular.menu').length) {
        $('.vi-ui.tabular.menu .item').vi_tab({history: true, historyType: 'hash'});
    }
    if ($('.vi-ui.accordion').length) {
        $('.vi-ui.accordion:not(.wplwl-accordion-init)').addClass('wplwl-accordion-init').vi_accordion('refresh');
    }
    $('.vi-ui.checkbox:not(.wplwl-checkbox-init)').addClass('wplwl-checkbox-init').off().checkbox();
    $('.vi-ui.dropdown:not(.wplwl-dropdown-init)').addClass('wplwl-dropdown-init').off().dropdown();
    /*Select White or Black list*/
    $('input[name="choose_using_white_black_list"]').on('change', function () {
        handle_choose_list($(this).val());
    });
    handle_choose_list($('input[name="choose_using_white_black_list"]:checked').val());

    function handle_choose_list(intent) {
        let $white_list = $('[name="white_list"]').closest('tr');
        let $black_list = $('[name="black_list"]').closest('tr');
        switch (intent) {
            case 'white_list':
                $black_list.hide();
                $white_list.show();
                break;
            case 'black_list':
                $white_list.hide();
                $black_list.show();
                break;
        }
    }
    $('.wheel-settings .ui-sortable').sortable({
        update: function (event, ui) {
            indexChangeCal();
        }
    });
    $('#gdpr_policy,#enable_funnelkit,#enable_sendy,#enable_mailster,#enable_mailpoet,#mailchimp_enable, #wplwl_enable_active_campaign, #wplwl_sendgrid_enable, #enable_sendinblue, #enable_klaviyo,#enable_hubspot, #metrilo_enable').on('change', function () {
        if ($(this).prop('checked')){
           $('.wplwl-'+$(this).attr('id')+'-class').show() ;
        }else {
            $('.wplwl-'+$(this).attr('id')+'-class').hide();
        }
    }).trigger('change');
    /*Select intent*/
    $('select[name="notify_intent"]').on('change', function () {
        handle_select_intent_value($(this).val());
    });
    handle_select_intent_value($('select[name="notify_intent"]').val());

    function handle_select_intent_value(intent) {
        let $ini_time = $('input[name="show_wheel"]').closest('tr');
        let $scroll_amount = $('input[name="scroll_amount"]').closest('tr');
        switch (intent) {
            case 'popup_icon':
            case 'show_wheel':
                $ini_time.closest('tr').fadeIn(200);
                $scroll_amount.closest('tr').fadeOut(200);
                break;
            case 'random':
                $ini_time.closest('tr').fadeIn(200);
                $scroll_amount.closest('tr').fadeIn(200);
                break;
            case 'on_scroll':
                $ini_time.closest('tr').fadeOut(200);
                $scroll_amount.closest('tr').fadeIn(200);
                break;
            case 'on_exit':
                $ini_time.closest('tr').fadeOut(200);
                $scroll_amount.closest('tr').fadeOut(200);
                break;
        }
    }

    /*select recaptcha version */
    jQuery('.wplwl_recaptcha_version').dropdown({
        onChange: function (val) {
            if (val == 2) {
                jQuery('.wplwl-recaptcha-v2-wrap').show();
                jQuery('.wplwl-recaptcha-v3-wrap').hide();
            } else {
                jQuery('.wplwl-recaptcha-v2-wrap').hide();
                jQuery('.wplwl-recaptcha-v3-wrap').show();
            }
        }
    });
    
    /*Color picker*/
    $('.color-picker').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
        },
        hide: true,
        border: true
    }).on('click', function () {
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });

    $('body').on('click', function () {
        $('.iris-picker').hide();
    });
    $('.color-picker').on('click', function (event) {
        event.stopPropagation();
    });
    /*Select popup icon*/
    $('.wheel-popup-icon').on('click', function () {
        let $button = $(this), $container = $button.closest('.wheel-popup-icons-container');
        if ($button.hasClass('wheel-popup-icon-selected')) {
            $button.removeClass('wheel-popup-icon-selected').attr('style', '');
            $container.find('input[name="wheel_popup_icon"]').val('');
        } else {
            let $selected = $container.find('.wheel-popup-icon-selected');
            $container.find('input[name="wheel_popup_icon"]').val($button.data('wheel_popup_icon'));
            $button.addClass('wheel-popup-icon-selected').attr('style', $selected.attr('style'));
            $selected.attr('style', '').removeClass('wheel-popup-icon-selected');
        }
    });


    $('#wheel_popup_icon_color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            $('.wheel-popup-icon-selected').css({color: ui.color.toString()});
        },
        hide: true,
        border: true
    });
    $('#wheel_popup_icon_bg_color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            $('.wheel-popup-icon-selected').css({'background-color': ui.color.toString()});
        },
        hide: true,
        border: true
    });
    $(document).on('click','.clone_piece', function () {
        let new_row = $(this).parent().parent().clone();
        let new_val = parseInt(new_row.find('input[name="probability[]"]').val());
        new_row.find('input[name="probability[]"]').val(new_val);
        new_row.insertAfter($(this).parent().parent());
        indexChangeCal();
        changes_probability();
        new_row.find('.vi-ui.dropdown').dropdown({placeholder: ''});
        new_row.find('.color-picker').iris({
            change: function (ev, uis) {
                $(this).parent().find('.color-picker').css({backgroundColor: uis.color.toString()});
            },
            hide: true,
            border: true,
            width: 270
        }).on('click', function (e) {
            e.stopPropagation();
        });
    });
    $(document).on('click','.remove_field', function () {
        changes_probability();
        if (confirm("Would you want to remove this?")) {
            if ($('.wheel_col').length > 3) {
                $(this).closest('tr').remove();
                changes_probability();
                indexChangeCal();
            } else {
                alert('Must have at least 3 columns!');
                return false;
            }
        }
    });

    changes_probability();
    $(document).on('change', '.prize_quantity', function () {
        changes_probability();
    });
    $(document).on('change', '.probability', function () {
        changes_probability();
    });
    function changes_probability() {// check probability
        let tong = 0;
        let $probability = $('.probability');
        $probability.each(function () {
            let $current = $(this);
            let $row = $current.closest('tr');
            let $coupon_type = $row.find('select[name="coupon_type[]"]');
            let $prize_quantity = $row.find('input[name="prize_quantity[]"]');
            if ($coupon_type.val() === 'non' || parseInt($prize_quantity.val()) !== 0) {
                tong += parseInt($current.val());
            }
        });
        if (tong > 0) {
            $probability.each(function () {
                let $current = $(this);
                let $row = $current.closest('tr');
                let $coupon_type = $row.find('select[name="coupon_type[]"]');
                let $prize_quantity = $row.find('input[name="prize_quantity[]"]');
                let percent = 0;
                if ($coupon_type.val() === 'non' || parseInt($prize_quantity.val()) !== 0) {
                    let weigh = parseInt($current.val());
                    percent = roundResult(weigh * 100 / tong);
                }
                $row.find('.probability-percent').val(percent);
            });
        } else {
            $probability.each(function () {
                let $current = $(this);
                $current.closest('tr').find('.probability-percent').val(0);
            });
        }
    }

    $('.wplwl-button-save-settings-container').closest('form').on('submit', function () {
        let $label = $('.custom_type_label');
        let $coupon_type = $('select[name="coupon_type[]"]');
        for (let i = 0; i < $label.length; i++) {
            if ($label.eq(i).val() === '') {
                alert('Label cannot be empty.');
                $label.eq(i).focus();
                return false;

            }
            if ($coupon_type.eq(i).val() === 'custom' && $('.custom_type_value').eq(i).val() === '') {
                alert('Value cannot be empty.');
                $('.custom_type_value').eq(i).focus();
                return false;

            }
        }

        $('.wplwl-button-save-settings-container').find('button').addClass('loading');
    });

    function indexChangeCal() {
        let i = 1;
        $('.wheel-col-index').map(function () {
            $(this).html(i);
            i++;
        })
    }

    indexChangeCal();
    $(document).on('change', 'select[name="prize_type[]"]', function () {
        changes_probability();
        let $coupon_type = $(this);
        let $row = $coupon_type.closest('tr');
        let coupon_type = $coupon_type.val();
        switch (coupon_type) {
            case 'non':
                $row.attr('class', `wheel_col wheel_col-${coupon_type}`);
                $row.find('.custom_type_label').val('Not Lucky');
                $row.find('select[name="email_templates[]"]').val('').trigger('change');
                break;
            case 'custom':
                $row.attr('class', `wheel_col wheel_col-${coupon_type}`);
                $row.find('.custom_type_value').val('');
                $row.find('.custom_type_label').val('');
                break;
            default:
        }
    });

    $('.wplwl_color_palette').on('click', function () {
        let color_code = $(this).data('color_code');
        let color_array = [],color_des = $('.color_palette').data('color_arr')[color_code];
        if (color_des?.pointer){
            $('#pointer_color').val(color_des.pointer).trigger('change');
        }
        $('#wheel_wrap_bg_color').val(color_code).trigger('change');
        if (color_des?.color && color_des.color.length){
            color_array = color_des.color;
        }
        let piece_color = $('.wheel_col').find('input[name="bg_color[]"]').map(function () {
            return $(this).val();
        }).get();
        let color_size = color_array.length,piece_size = piece_color.length,i, j = 0;
        for (i = 0; i < piece_size; i++) {
            if (j == color_size) {
                j = 0;
            }
            $('.wheel_col').find('input[name="bg_color[]"]').eq(i).val(color_array[j]).css({'background-color': color_array[j]});
            j++;
        }
        $('.auto_color_ok').on('click', function () {
            $('.color_palette').hide();
            $('.auto_color_ok_cancel').hide();
            $('.auto_color').show();
        });
        $('.auto_color_cancel').on('click', function () {
            j = 0;
            for (i = 0; i < piece_size; i++) {
                if (j == color_size) {
                    j = 0;
                }
                $('.wheel_col').find('input[name="bg_color[]"]').eq(i).val(piece_color[j]).css({'background-color': piece_color[j]});
                j++;
            }
            $('.color_palette').hide();
            $('.auto_color_ok_cancel').hide();
            $('.auto_color').show();
        })
    });
    $('.auto_color').on('click', function () {
        $('.color_palette').css({'display': 'flex'});
        $('.auto_color_ok_cancel').css({'display': 'inline-block'});
        $(this).hide();
        $('.auto_color_ok').on('click', function () {
            $('.color_palette').hide();
            $('.auto_color_ok_cancel').hide();
            $('.auto_color').show();
        });
        $('.auto_color_cancel').on('click', function () {
            $('.color_palette').hide();
            $('.auto_color_ok_cancel').hide();
            $('.auto_color').show();
        })
    });

    function roundResult(number) {
        let temp = Math.pow(10, 2);
        return Math.round(number * temp) / temp;
    }

});

jQuery(document).ready(function ($) {
    'use strict';
    // Set all variables to be used in scope
    var frame,
        metaBox = $('#wplwl-bg-image'), // Your meta box id here
        addImgLink = metaBox.find('.wplwl-upload-custom-img'),
        imgContainer = metaBox.find('.wplwl-image-container');
    $('.wheel_wrap_bg_image_custom').css('margin-top','15px');
    $('.wheel_wrap_bg_image_type').dropdown({
        onChange:function (val) {
            handle_choose_bg_image_type(val);
        }
    });
    handle_choose_bg_image_type($('.wheel_wrap_bg_image_type select').val())
    function handle_choose_bg_image_type(val){
        if (parseInt(val)){
            $('.wheel_wrap_bg_image_custom').show();
            if ($('.wplwl-remove-image').length){
                $('.wplwl-upload-custom-img').hide();
            }else {
                $('.wplwl-upload-custom-img').show();
            }
        }else {
            $('.wheel_wrap_bg_image_custom').hide();
            $('.wheel_wrap_bg_image').val(wp_lucky_wheel_params_admin.bg_img_default);
        }
    }
    $(document).on('click', '.wplwl-remove-image',function (event) {
        event.preventDefault();
        $(this).parent().html('');
        $('.wplwl-upload-custom-img').show();
    })
    // ADD IMAGE LINK
    addImgLink.on('click', function (event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on('select', function () {

            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            console.log(attachment);
            var attachment_url;
            if (attachment.sizes.thumbnail) {
                attachment_url = attachment.sizes.thumbnail.url;
            } else if (attachment.sizes.medium) {
                attachment_url = attachment.sizes.medium.url;
            } else if (attachment.sizes.large) {
                attachment_url = attachment.sizes.large.url;
            } else if (attachment.url) {
                attachment_url = attachment.url;
            }
            // Send the attachment URL to our custom image input field.
            imgContainer.html('<img style="border: 1px solid;width: 300px;"class="review-images" src="' + attachment_url + '"/><input class="wheel_wrap_bg_image" name="wheel_wrap_bg_image" type="hidden" value="' + attachment.id + '"/><span class="wplwl-remove-image nagative vi-ui button">Remove</span>');

            $('.wplwl-upload-custom-img').hide();

        });

        // Finally, open the modal on click
        frame.open();
    });
    // DELETE IMAGE LINK

    $('.wplwl-remove-image').on('click', function (event) {
        event.preventDefault();
        $(this).parent().html('');
        $('.wplwl-upload-custom-img').show();
    });


});
jQuery(document).ready(function ($) {
    'use strict';
    // Set all variables to be used in scope
    var frame1,
        metaBox1 = $('#wplwl-bg-image1'), // Your meta box id here
        addImgLink1 = metaBox1.find('.wplwl-upload-custom-img1'),
        imgContainer1 = metaBox1.find('#wplwl-new-image1');

    // ADD IMAGE LINK
    addImgLink1.on('click', function (event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame1) {
            frame1.open();
            return;
        }

        // Create a new media frame
        frame1 = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame1.on('select', function () {

            // Get media attachment details from the frame state
            var attachment1 = frame1.state().get('selection').first().toJSON();
            console.log(attachment1);
            var attachment_url1;
            if (attachment1.sizes.thumbnail) {
                attachment_url1 = attachment1.sizes.thumbnail.url;
            } else if (attachment1.sizes.medium.url) {
                attachment_url1 = attachment1.sizes.medium;
            } else if (attachment1.sizes.large.url) {
                attachment_url1 = attachment1.sizes.large;
            } else if (attachment1.url) {
                attachment_url1 = attachment1.url;
            }

            // Send the attachment URL to our custom image input field.
            imgContainer1.append('<div class="wplwl-image-container1"><img style="border: 1px solid;"class="review-images" src="' + attachment_url1 + '"/><input class="wheel_center_image" name="wheel_center_image" type="hidden" value="' + attachment1.id + '"/><span class="wplwl-remove-image1 nagative vi-ui button">Remove</span></div>');

            $('.wplwl-upload-custom-img1').hide();
            $('.wplwl-remove-image1').on('click', function (event) {
                event.preventDefault();
                $(this).parent().html('');
                $('.wplwl-upload-custom-img1').show();
            })

        });

        // Finally, open the modal on click
        frame1.open();
    });
    // DELETE IMAGE LINK

    $('.wplwl-remove-image1').on('click', function (event) {
        event.preventDefault();
        $(this).parent().html('');
        $('.wplwl-upload-custom-img1').show();
    });
    $(".wplwl-ac-search-list").select2({
        placeholder: "Type list name",
        ajax: {
            url: "admin-ajax.php?action=wplwl_search_active_campaign_list",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1,
        allowClear: true
    });

//    select google font
    $('#wplwl-google-font-select').fontselect().change(function () {
        // replace + signs with spaces for css
        $('#wplwl-google-font-select').val($(this).val());
        $('.wplwl-google-font-select-remove').show();
    });
    $('.wplwl-google-font-select-remove').on('click', function () {
        $(this).parent().find('.font-select span').html('<span>Select a font</span>');
        $('#wplwl-google-font-select').val('');
        $(this).hide();
    })

    /*Color picker*/
    $('#wplwl_button_shop_color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
        },
        hide: true,
        border: true
    }).on('click', function (event) {
        event.stopPropagation();
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });
    $('#wplwl_button_shop_bg_color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
        },
        hide: true,
        border: true
    }).on('click', function (event) {
        event.stopPropagation();
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });

    $('.preview-emails-html-overlay').on('click', function () {
        $('.preview-emails-html-container').addClass('preview-html-hidden');
    })
    $('.wplwl-preview-emails-button').on('click', function () {
        $('.wplwl-preview-emails-button').html('Please wait...');
        let wplwl_language = $(this).data('wplwl_language') || '';
        $.ajax({
            url: wp_lucky_wheel_params_admin.url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                action: 'wplwl_preview_emails',
                heading: $('#heading'+wplwl_language).val(),
                content: tinyMCE.get('content'+wplwl_language) ? tinyMCE.get('content'+wplwl_language).getContent() : $('#content'+wplwl_language).val(),
                from_name: $('#from_name'+wplwl_language).val(),
                footer_text: $('#footer_text'+wplwl_language).val(),
                email_base_color: $('#email_base_color').val(),
                email_background_color: $('#email_background_color').val(),
                email_body_background_color: $('#email_body_background_color').val(),
                email_body_text_color: $('#email_body_text_color').val(),
            },
            success: function (response) {
                $('.wplwl-preview-emails-button').html('Preview emails');
                if (response) {
                    $('.preview-emails-html').html(response.html);
                    $('.preview-emails-html-container').removeClass('preview-html-hidden');
                }
            },
            error: function (err) {
                $('.wplwl-preview-emails-button').html('Preview emails');
            }
        })
    })
    /*preview wheel*/
    $('.wordpress-lucky-wheel-preview-overlay').on('click', function () {
        $('.wordpress-lucky-wheel-preview').addClass('preview-html-hidden');
    })
    $('.preview-lucky-wheel').on('click', function () {
        let color = [];
        $('input[name="bg_color[]"]').map(function () {
            color.push($(this).val());
        });
        let slices_text_color = [];
        $('input[name="slices_text_color[]"]').map(function () {
            slices_text_color.push($(this).val());
        });
        let label = [];
        let quantity_label = $('input[name="quantity_label"]').val();
        $('input[name="custom_type_label[]"]').map(function () {
            let $current_label = $(this);
            let $row = $current_label.closest('tr');
            let $prize_quantity = $row.find('.prize_quantity');
            let prize_quantity = parseInt($prize_quantity.val());
            let current_label = $current_label.val();
            if ($row.find('select[name="prize_type[]"]').val() !== 'non' && prize_quantity > 0) {
                current_label = current_label.replace('{quantity_label}', quantity_label.replace('{prize_quantity}', prize_quantity));
            } else {
                current_label = current_label.replace('{quantity_label}', '').replace('{prize_quantity}', '');
            }
            label.push(current_label);
        });
        let prize_type = [];
        $('select[name="prize_type[]"]').map(function () {
            prize_type.push($(this).val());
        });
        let coupon_amount = [];
        $('input[name="coupon_amount[]"]').map(function () {
            coupon_amount.push($(this).val());
        });
        let font_size = $('#font_size').val();
        let wplwl_center_color = $('#wheel_center_color').val();
        let wplwl_border_color = $('#wheel_border_color').val();
        let wplwl_dot_color = $('#wheel_dot_color').val();
        let slices = color.length;
        let sliceDeg = 360 / slices;
        let deg = -(sliceDeg / 2);
        let cv = document.getElementById('wplwl_canvas');
        let ctx = cv.getContext('2d');
        let width = 400;// size
        cv.width = width;
        cv.height = width;
        let center = (width) / 2;
        let wheel_text_size = parseInt(width / 28) * parseInt(font_size) / 100;
        for (let i = 0; i < slices; i++) {
            drawSlice(ctx, deg, color[i]);
            drawText(ctx, deg + sliceDeg / 2, label[i], slices_text_color[i], wheel_text_size);
            deg += sliceDeg;

        }
        cv = document.getElementById('wplwl_canvas1');
        ctx = cv.getContext('2d');
        cv.width = width;
        cv.height = width;
        drawPoint(ctx, deg, wplwl_center_color);
        let center_image = $('input[name="wheel_center_image"]').parent().find('img').attr('src');
        if (center_image) {
            let wl_image = new Image;
            wl_image.onload = function () {
                cv = document.getElementById('wplwl_canvas1');
                ctx = cv.getContext('2d');
                let image_size = 2 * (width / 8 - 7);
                ctx.arc(center, center, image_size / 2, 0, 2 * Math.PI);
                ctx.clip();
                ctx.drawImage(wl_image, center - image_size / 2, center - image_size / 2, image_size, image_size);

            };
            wl_image.src = center_image;
        }
        drawBorder(ctx, wplwl_border_color, 'rgba(0,0,0,0)', 20, 4, 5, 'rgba(0,0,0,0.2)');
        cv = document.getElementById('wplwl_canvas2');
        ctx = cv.getContext('2d');

        cv.width = width;
        cv.height = width;
        drawBorder(ctx, 'rgba(0,0,0,0)', wplwl_dot_color, 20, 4, 5, 'rgba(0,0,0,0)');

        $('.wordpress-lucky-wheel-preview').removeClass('preview-html-hidden');

        function deg2rad(deg) {
            return deg * Math.PI / 180;
        }

        function drawSlice(ctx, deg, color) {
            ctx.beginPath();
            ctx.fillStyle = color;
            ctx.moveTo(center, center);
            let r;
            if (width <= 480) {
                r = width / 2 - 10;
            } else {
                r = width / 2 - 14;
            }
            ctx.arc(center, center, r, deg2rad(deg), deg2rad(deg + sliceDeg));
            ctx.lineTo(center, center);
            ctx.fill();
        }

        function drawPoint(ctx, deg, color) {
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

        function drawBorder(ctx, borderC, dotC, lineW, dotR, des, shadColor) {
            ctx.beginPath();
            ctx.strokeStyle = borderC;
            ctx.lineWidth = lineW;
            ctx.shadowBlur = 1;
            ctx.shadowOffsetX = 8;
            ctx.shadowOffsetY = 8;
            ctx.shadowColor = shadColor;
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

        function drawText(ctx, deg, text, color, wheel_text_size) {
            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(deg2rad(deg));
            ctx.textAlign = "right";
            ctx.direction = 'rtl';
            ctx.fillStyle = color;
            ctx.font = '400 ' + wheel_text_size + 'px shabnam';
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

    });
    /**
     * Start Get download key
     */
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = JSON.parse(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
    /**
     * End get download key
     */
});
