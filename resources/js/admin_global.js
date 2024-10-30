jQuery(document).ready(function ($) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    if( cmg_infos.cmg_version_type == 'wp' ){
        $('body').addClass( 'cmg-non-pro' );
    }

    if( urlParams.get('post_type') == 'cmg_magnet' ){
        $('.page-title-action').attr('href', $('.page-title-action').attr('href')+'&classic-editor__forget');

        $('.wp-list-table td.title a').each(function(index, el){
            let a_href = decodeURI( $(el).attr('href') );
            if( a_href.search('&classic-editor') >= 0 ){
                if( a_href.search('&classic-editor__forget') < 0  ) {
                    $(el).attr('href', a_href.replace('&classic-editor','&classic-editor__forget'));
                } else {
                    if( a_href.search('&classic-editor&classic-editor__forget') >= 0 )
                        $(el).parent('span').remove();
                }
            }
        });
    }
    if( ($('body').hasClass('post-php') && $('body').hasClass('post-type-cmg_magnet') ) || ($('body').hasClass('post-new-php') && urlParams.get('post_type') == 'cmg_magnet') ){
        if( !$('body.block-editor-page').length ){
            location.href = window.location.href.replace('&classic-editor','')+'&classic-editor__forget';
        }
    }
});