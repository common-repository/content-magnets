jQuery(document).ready(function ($) {
    $('.cmg_style_generator').each(function(index, el){
        let css_rules = '<style>'+$(el).html()+'</style>';
        $(css_rules).insertBefore( $(el).parents('.content_magnet_form_div') );
    });
    $('form.cmg_form_signup').find('.cmg_submit_btn').append('<i class="fa fa-spinner" aria-hidden="true"></i>');
    $('form.cmg_form_signup').submit(function (event) {

        event.preventDefault();
        var cmg_form = $(this);
        if (cmg_form.hasClass('waiting_response'))
            return false;

        cmg_form.addClass('waiting_response');
        var magnet_email = cmg_form.find('input[name=cmg_email]').val();
        var magnet_id = cmg_form.data('magnet_id');
        var formData = {
            'action': 'cmg_magnet_me',
            'magnet_post_id': magnet_id,
            'magnet_email': magnet_email,
        };
        if (!cmg_form.parents('.cmg_form_block_inner').find('.cmg_response').length)
            cmg_form.parents('.cmg_form_block_inner').append('<div class="cmg_response">\n' +
                '<i class="fa fa-check-circle" aria-hidden="true"></i>\n' +
                '<div class="cmg_response_txt">  </div>\n' +
                '<span class="cmg_close_response"> <i class="fa fa-times" aria-hidden="true"></i> </span>\n' +
                '</div>');

        $.post(cmg_infos.ajax_url, formData).done(function (data) {
            data = JSON.parse(data);
            if (data.status == 1) {
                let msg_response = data.html
                cmg_setCookie('cmg_magnet_email', magnet_email, 31);
                if (cmg_form.find('.cmg_redirectURL').lenght) {
                }
                //if( cmg_form.hasClass('single_cmg_form') ) {} else { }
                if( magnet_id == 'archive' ){
                    show_msg_response(msg_response, 'success', cmg_form.parents('.cmg_archive_action_div'));
                    window.location.reload(); return;
                }
                if (data.successMessageEnable){
                    show_msg_response(msg_response, 'success', cmg_form.parents('.i_cmg_item_inner'));
                } else {
                    if( data.download_resource_html )
                        show_msg_response(data.download_resource_html, 'success', cmg_form.parents('.i_cmg_item_inner'));
                }
                if (data.resourceURL) {
                    window.open(data.resourceURL, '_blank');
                    if( data.download_resource_html )
                        msg_response+=data.download_resource_html;
                }
                if (data.redirectUrlEnable && data.redirectURL) {
                    window.location.href = data.redirectURL;
                }

                if (!cmg_form.hasClass('single_cmg_form')) {
                    window.location.reload();
                }

            } else {
                show_msg_response(data.html, 'error', cmg_form.parents('.i_cmg_item_inner'));
            }

            cmg_form.removeClass('waiting_response');
        });

        event.preventDefault();
        return false;
    });

    if( cmg_infos.cmg_version_type == 'wp' ){
        $('.cmg_form_block_inner').append( cmg_infos.co_branded );
    }

    $('body').on('click', '.cmg_close_response', cmg_close_response);

    function cmg_close_response() {
        $(this).parent('.cmg_response').hide();
    }

    function show_msg_response(r_message = '', r_type = 'loading', cmg_form) {
        cmg_form.find('.cmg_response_txt').html(r_message).parent().removeClass('cmg_resp_loading cmg_resp_success cmg_resp_error').addClass('cmg_resp_' + r_type).showInlineBlock();
        if (r_type == 'success') {
            cmg_form.find('.cmg_form_signup').after(cmg_form.find('.cmg_response')).remove();
        }
    }

    $(window).resize(cmg_resize);

    function cmg_resize() {
        var cmg_min_h = 100;
        $('#cmg_archive .cmg_item_body').removeClass('cmg_item_resized');
        $('#cmg_archive .cmg_item_body').each(function (index, el) {
            if ($(el).outerHeight() > cmg_min_h)
                cmg_min_h = $(el).outerHeight();
        });
        $('#cmg_archive .cmg_item_body').css('height', cmg_min_h + 'px').addClass('cmg_item_resized');
    }

    cmg_resize();
});


//GX global functions
function cmg_setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/;";
}

function cmg_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function cmg_is_Numeric(num) {
    return !isNaN(num)
}

jQuery.fn.showInlineBlock = function () {
    return this.css('display', 'inline-flex');
};