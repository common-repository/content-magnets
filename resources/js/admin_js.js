jQuery(document).ready(function ($) {
    var thiss, t_parent_el;

    $('#submitdiv').parent('.meta-box-sortables').removeClass('meta-box-sortables').addClass('cmg_fixed_metabox');
    $('#submitdiv').find('h2').attr('class', '');

    $('.i_toggle_a').click(i_toggle_a);

    function i_toggle_a(e) {
        toggle_div = $($(this).attr('href'));
        if (toggle_div.length)
            toggle_div.slideToggle();
        e.preventDefault();
        return false;
    }

    $('#cmg_create_archive_page').click(cmg_create_archive_page);



    function cmg_create_archive_page() {

        var cmg_page_title = $('#cmg_page_title').val();
        var cmg_page_slug = $('#cmg_page_slug').val();
        var cmg_page_description = $('#cmg_page_description').val();
        var formData = {
            'action': 'cmg_create_archive_page',
            'cmg_page_title': cmg_page_title,
            'cmg_page_slug': cmg_page_slug,
            'cmg_page_description': cmg_page_description
        };

        show_msg_response(cmg_infos.loadingmessage, 'loading');

        $.post(cmg_infos.ajax_url, formData).done(function (data) {
            data = JSON.parse(data);
            if (data.status == 1) {
                $('#field_cmg_archive_option-cmg_archive_page').append('<option value="' + data.page_id + '">' + cmg_page_title + '</option>').val(data.page_id);
                //window.location.reload();
                show_msg_response(data.html, 'success');
            } else {
                show_msg_response(data.html, 'error');
            }
        });

        event.preventDefault();
        return false;
    }

    $('.cmg_close_response').click(cmg_close_response);

    function cmg_close_response() {
        $(this).parent('.cmg_response').hide();
    }

    function show_msg_response(r_message = '', r_type = 'loading') {
        $('.cmg_response_txt').html(r_message).parent().removeClass('cmg_resp_loading cmg_resp_success cmg_resp_error').addClass('cmg_resp_' + r_type).showInlineBlock();
    }


    $('#cmg_export_magnets').click(cmg_export_magnets);

    function cmg_export_magnets(e) {
        e.preventDefault();
        var thiss = $(this);
        if( thiss.hasClass('cmg_wait_ajax') )
            return false;

        var cmg_export_from_magnets = 'all'; //$('#cmg_export_from_magnets').val(); //Pro only
        var formData = {
            'action': 'cmg_export_magnets',
            'cmg_export_from_magnets' : cmg_export_from_magnets
        };

        show_msg_response(cmg_infos.exportLoadingMessage, 'loading');

        thiss.addClass('cmg_wait_ajax');

        $.post(cmg_infos.ajax_url, formData).done(function (data) {
            data = JSON.parse(data);
            if (data.status == 1) {
                //window.location.href();
                location.href = decodeURIComponent( data.url );

                show_msg_response(data.html, 'success');
            } else {
                show_msg_response(data.html, 'error');
            }
        }).fail(function () {
        }).always(function () {
            thiss.removeClass('cmg_wait_ajax');
        });

        event.preventDefault();
        return false;
    }
    /*$('.i_parent_check_childs .acf-taxonomy-field').append( '<a href="#" class="i_acf_expand_toggle"> Expand/Collapse</a>' );
    $('.i_parent_check_childs .acf-taxonomy-field').append( '<a href="#" class="i_acf_clear_checkboxes"> Clear all</a>' );

    $('.i_parent_check_childs').on('click', '.i_acf_expand_toggle', i_acf_expand_toggle );
    function i_acf_expand_toggle(e){
        e.preventDefault();
        thiss = $('.i_parent_check_childs');
        if( thiss.hasClass('i_expanded') ){
            thiss.removeClass('i_expanded');
            $('html, body').animate({
                scrollTop: thiss.offset().top - 30
            }, 1000);
        } else {
            thiss.addClass('i_expanded');
        }
        return false;
    }

    $('.i_parent_check_childs').on('click', '.i_acf_clear_checkboxes', i_acf_clear_checkboxes );
    function i_acf_clear_checkboxes(e){
        e.preventDefault();
        thiss = $('.i_parent_check_childs');

        all_inputs = thiss.find('input[type=checkbox]');
        all_inputs.prop('checked', false);

        return false;
    }*/

    $('.i_sortorder_check_childs div.cmg_checkbox_div > label > input:checked').each(function( index,el){
        t_parent_el = $(this).parent('label').parent('.cmg_checkbox_div');
        $(this).parents('.i_sortorder_check_childs').prepend( t_parent_el );
    });
});
jQuery.fn.showInlineBlock = function () {
    return this.css('display', 'inline-flex');
};