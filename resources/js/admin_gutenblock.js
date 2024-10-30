(function (blocks, editor, components, i18n, element, hooks) {
    var __ = i18n.__
    var el = element.createElement
    var registerBlockType = blocks.registerBlockType
    var RichText = editor.RichText
    var BlockControls = editor.BlockControls
    //var AlignmentToolbar = editor.AlignmentToolbar
    var MediaUpload = editor.MediaUpload
    var InspectorControls = editor.InspectorControls
    var PanelBody = components.PanelBody
    var TextControl = components.TextControl
    var CheckboxControl = components.CheckboxControl
    var ToggleControl = components.ToggleControl
    var ColorPalette = components.ColorPalette
    var PanelColorSettings = editor.PanelColorSettings
    var SelectControl = components.SelectControl
    var Spinner  = components.Spinner
    var magnets_data = cmg_global.magnets_data;
    var CMG_NAME = cmg_infos.CMG_NAME;
    var first_init = true;
    var cmg_magnet_sizes = [
        { 'label' : 'Default', 'value': '10' },
        { 'label' : '50%', 'value': '6' },
        { 'label' : '75%', 'value': '8' },
        { 'label' : '100%', 'value': '12' },
    ];

    var $=jQuery;
    function show_loading(){
        $('.cmg_loading_div').removeClass('cmg_hidden');
    }
    function hide_loading(){
        $('.cmg_loading_div').addClass('cmg_hidden');
    }
    var cmg_attributes = { // Necessary for saving block content.
        reRender: {
            type: 'boolean',
            default: true
        },
        title: {
            type: 'string',
            //source: 'html',
            selector: 'h3'
        },
        subtitle: {
            type: 'string',
            //source: 'html',
            selector: 'p'
        },
        mediaID: {
            type: 'number'
        },
        cmg_id: {
            type: 'number'
        },
        mediaURL: {
            type: 'string',
            //source: 'attribute',
            selector: 'img',
            attribute: 'src'
        },
        magnetSize: {
            type: 'string'
        },
        additionalClassName: {
            type: 'string'
        },
        buttonTxt: {
            type: 'string'
        },
        resourceURL: {
            type: 'string'
        },
        redirectURL: {
            type: 'string'
        },
        redirectUrlEnable: {
            type: 'boolean'
        },
        excludeFromArchive: {
            type: 'boolean'
        },
        successMessageEnable: {
            type: 'boolean'
        },
        firstNameEnable: {
            type: 'boolean'
        },
        lastNameEnable: {
            type: 'boolean'
        },
        magnetImageEnable: {
            type: 'boolean',
            default: true
        },
        downloadImmediately: {
            type: 'boolean'
        },
        bgColor: {
            type: 'string'
        },
        titleColor: {
            type: 'string'
        },
        txtColor: {
            type: 'string'
        },
        btnColor: {
            type: 'string'
        },
        btnTxtColor: {
            type: 'string'
        },
        btnColorHover: {
            type: 'string'
        },
        btnTxtColorHover: {
            type: 'string'
        },
        SMbgColor: {
            type: 'string'
        },
        emailAddress: {
            type: 'string'
        },
        magnetPost: {
            type: 'number'
        },
        successMessage: {
            type: 'string',
        },
    };

    function prevent_undefined(t_val, empty_val = '') {
        return (typeof t_val !== 'undefined' && t_val != '') ? t_val : empty_val;
    }

    function is_url(str) {
        regexp = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
        if (regexp.test(str)) {
            return true;
        } else {
            return false;
        }
    }

    const cmg_form_icon = el('svg', {
            className: 'dashicon dashicons-edit',
            width: '20',
            height: '20',
            'viewBox': '0 0 32 32'
        },
        el('path', {d: 'M18.2,12.4h6.6c0.5,0,0.8-0.4,0.8-0.8V6.3c0-0.5-0.4-0.8-0.8-0.8h-6.6c-7,0-11.9,4.3-11.9,10.6c0,6.2,4.9,10.5,11.9,10.5h1\n' +
                '\tc0,0,0.1,0,0.1,0h5.4c0.5,0,0.8-0.4,0.8-0.8v-5.3c0-0.5-0.4-0.8-0.8-0.8c-0.5,0-0.8,0.4-0.8,0.8v4.4h-3.8v-3.6h1.9\n' +
                '\tc0.5,0,0.8-0.4,0.8-0.8c0-0.5-0.4-0.8-0.8-0.8h-3.9c-4.1,0-4.9-1.4-4.9-3.6C13.3,13.8,14.2,12.4,18.2,12.4z M18.2,21.3h0.3v3.6h-0.3\n' +
                '\tc-3,0-5.6-0.9-7.5-2.5C9,20.7,8,18.5,8,16c0-2.5,0.9-4.7,2.7-6.3c1.8-1.7,4.4-2.5,7.5-2.5H24v3.6h-3.7V8.4c0-0.5-0.4-0.8-0.8-0.8\n' +
                '\ts-0.8,0.4-0.8,0.8v2.3h-0.4c-4.4,0-6.6,1.7-6.6,5.3S13.8,21.3,18.2,21.3z'}),
    );

    function magnetPost_selected( cmg_post_id, props ){

        var formData = {
            'action': 'cmg_magnet_post_selected',
            'cmg_post_id': cmg_post_id
        };
        show_loading();
        $.post(cmg_infos.ajax_url, formData).done(function (data) {
            var data = JSON.parse(data);
            var magnet_info = data.magnet_info;
            if (data.status == '1') {
                if( magnet_info.hasOwnProperty('cmg_id') )
                    magnet_info.cmg_id = Number(magnet_info.cmg_id);
                if( magnet_info.hasOwnProperty('magnetPost') )
                    magnet_info.magnetPost = Number(magnet_info.magnetPost);


                props.setAttributes(magnet_info);
            } else {

            }
            hide_loading();
        });
    }

    var resolver_interval;
    var cmg_in_single_magnet = Number( cmg_gutenblock_info.cmg_in_single_magnet );

    function resolve_in_single_magnet(){
        if( cmg_in_single_magnet ){
            setTimeout(function(){
                if( cmg_in_single_magnet ){
                    $('.cmg_kill_when_guten_active').remove(); //edit-post-layout__metaboxes
                    if( $('.block-editor-warning__action').length ){
                        $('.block-editor-warning__action button').click();
                    }
                }
            }, 500);
        }
    }

    registerBlockType('cmg/form-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
        title: __('Content Magnet', CMG_NAME), // The title of our block.
        description: __('Create an irresistible offer to compel visitors to give you their email address.', CMG_NAME), // The description of our block.
        icon: cmg_form_icon, // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.
        supports: {
            //align: true,
            //alignWide: true
        },
        attributes: cmg_attributes,

        edit: function (props) {

            wp.data.dispatch('core/notices').removeNotice('CMG_WARNING_NOTICE'); //Remove all CMG warning messages

            var attributes = props.attributes;
            if( cmg_in_single_magnet ){
                attributes.cmg_id = Number(cmg_in_single_magnet);
                attributes.magnetPost = Number(cmg_in_single_magnet);

                props.setAttributes({magnetPost: Number(cmg_in_single_magnet)});
                props.setAttributes({cmg_id: Number(cmg_in_single_magnet)});
            } else if (!attributes.cmg_id && attributes.magnetPost) {
                attributes.cmg_id = Number(attributes.magnetPost);
                props.setAttributes({cmg_id: Number(attributes.magnetPost)});
            }
            var additionalClassName = 'cmg-col-10';

            // Check for warnings --
            if (!first_init) {
                isset_warnings = false;
                if (!isset_warnings && attributes.successMessageEnable && attributes.redirectUrlEnable) {
                    isset_warnings = true;
                    wp.data.dispatch('core/notices').createWarningNotice(
                        __('Success message will not be visible, because the page will redirect before website visitor is able to see it.', CMG_NAME),
                        {id: 'CMG_WARNING_NOTICE', isDismissible: true}
                    );
                }
                if (!isset_warnings && attributes.redirectURL && !is_url(attributes.redirectURL)) {
                    isset_warnings = true;
                    wp.data.dispatch('core/notices').createWarningNotice(
                        __('Please enter a valid Redirect URL!', CMG_NAME),
                        {id: 'CMG_WARNING_NOTICE', isDismissible: true}
                    );
                }
            }
            // -- Check for warnings

            var onSelectImage = function (media) {
                return props.setAttributes({
                    mediaURL: media.sizes.medium.url, //media.url
                    mediaID: media.id
                })
            }

            var onSelectDownloadFile = function (media) {
                return props.setAttributes({
                    resourceURL: media.url
                })
            }

            function onChangeAlignment(newAlignment) {
                props.setAttributes({alignment: newAlignment})
            }

            const letCheckMagnet = async function (newVal) {
                props.setAttributes({title: newVal});

                if (props.attributes.magnetPost || prevent_undefined(props.attributes.title) != '') {
                    $.each(magnets_data, function (index, val) {
                        if (val['value'] == Number( props.attributes.magnetPost ) ) {
                            magnets_data[index]['label'] = newVal;
                            return;
                        }
                    });
                    return;
                }

                var p_id = prevent_undefined(props.attributes.magnetPost, 0);
                var title = prevent_undefined(newVal);
                //var magnet_content = prevent_undefined(props.attributes.subtitle);

                const response = await wp.apiFetch({
                    path: '/cmg/v1/change_create_magnet/' + p_id, method: 'POST',
                    data: {
                        title: title
                    }
                }, {
                    cache: 'no-cache',
                    headers: {
                        'user-agent': 'WP CMG Block',
                        'content-type': 'application/json'
                    },
                    redirect: 'follow',
                    referrer: 'no-referrer',
                })
                    .then(
                        returned => {
                            if (returned.ok) return returned;
                            throw new Error('Network response was not ok.');
                        }
                    );

                //let data = await response.json();
                //data = data.data;
                let data = await response;

                if (data.status) {
                    if (p_id == 0) {
                        magnets_data.push({label: title, value: data.post_id});
                        props.setAttributes({magnetPost: Number( data.post_id ) });
                    }

                    props.setAttributes({cmg_id: Number( data.post_id )});
                }
            }


            function changeMagnetPost(newTitle) {

            }

            if (first_init){
                first_init = false;
            }
            if( cmg_infos.cmg_version_type == 'wp' ) {
                setTimeout( function(){$('.block-editor-block-inspector__advanced').remove();}, 500 );
            }

            return [

                el(BlockControls, {key: 'controls'}, // Display controls when the block is clicked on.
                    el('div', {className: 'components-toolbar'},
                        el(MediaUpload, {
                            onSelect: onSelectImage,
                            type: 'image',
                            render: function (obj) {
                                return el(components.Button, {
                                        className: 'components-icon-button components-toolbar__control',
                                        onClick: obj.open
                                    },
                                    // Add Dashicon for media upload button.
                                    el('svg', {className: 'dashicon dashicons-edit', width: '20', height: '20'},
                                        el('path', {d: 'M2.25 1h15.5c.69 0 1.25.56 1.25 1.25v15.5c0 .69-.56 1.25-1.25 1.25H2.25C1.56 19 1 18.44 1 17.75V2.25C1 1.56 1.56 1 2.25 1zM17 17V3H3v14h14zM10 6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zm3 5s0-6 3-6v10c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V8c2 0 3 4 3 4s1-3 3-3 3 2 3 2z'})
                                    ))
                            }
                        })
                    ),
                ),
                el(InspectorControls, {key: 'cmg-inspector'}, // Display the block options in the inspector panel.
                    el(PanelBody, {
                            title: __('Choose Magnet', CMG_NAME),
                            className: 'block_cmg_magnet_choose cmg_block_panel',
                            initialOpen: true
                        },
                        // Button Text field option.
                        el(SelectControl, {
                            label: __('', CMG_NAME),
                            value: Number(attributes.magnetPost),
                            id: "magnetPost_selector",
                            options: magnets_data,
                            onChange: function (newMagnetPost) {
                                props.setAttributes({magnetPost: Number(newMagnetPost)});
                                props.setAttributes({cmg_id: Number(newMagnetPost)});
                                magnetPost_selected(newMagnetPost, props);
                            }
                        }),
                    ),
                    el(PanelBody, {
                            title: __('Magnet Settings', CMG_NAME),
                            className: 'block_cmg_magnet_settings cmg_block_panel',
                            initialOpen: true
                        },
                        el(ToggleControl, {
                            label: __('Magnet Image', CMG_NAME),
                            checked: attributes.magnetImageEnable,
                            onChange: function (newMagnetImageEnable) {
                                if (newMagnetImageEnable) {
                                    props.setAttributes({magnetImageEnable: true});
                                } else {
                                    props.setAttributes({magnetImageEnable: false});
                                }
                            }
                        }),
                        // Button Text field option.
                        el(TextControl, {
                            label: __('Button Text (Optional)', CMG_NAME),
                            value: attributes.buttonTxt,
                            onChange: function (newButtonTxt) {
                                props.setAttributes({buttonTxt: newButtonTxt})
                            }
                        }),
                        el('h3', {}, __('Actions', CMG_NAME)),
                        el(ToggleControl, {
                            label: __('Redirect to URL', CMG_NAME),
                            //value: attributes.firstNameEnable,
                            checked: attributes.redirectUrlEnable,
                            onChange: function (newRedirectUrlEnable) {
                                if (newRedirectUrlEnable) {
                                    props.setAttributes({redirectUrlEnable: true});
                                } else {
                                    props.setAttributes({redirectUrlEnable: false});
                                }
                            }
                        }),
                        el(TextControl, {
                            label: __('', CMG_NAME),
                            placeholder: 'Redirect URL',
                            value: attributes.redirectURL,
                            onChange: function (newRedirectURLl) {
                                props.setAttributes({redirectURL: newRedirectURLl})
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Success message', CMG_NAME),
                            //value: attributes.firstNameEnable,
                            checked: attributes.successMessageEnable,
                            onChange: function (newSuccessMessageEnable) {
                                if (newSuccessMessageEnable) {
                                    props.setAttributes({successMessageEnable: true});
                                } else {
                                    props.setAttributes({successMessageEnable: false});
                                }
                            }
                        }),
                        el(TextControl, {
                            label: __('', CMG_NAME),
                            placeholder: 'Type Success Message here...',
                            value: attributes.successMessage,
                            onChange: function (newSuccessMessage) {
                                props.setAttributes({successMessage: newSuccessMessage})
                            }
                        }),
                        el('h3', {}, __('Download Content', CMG_NAME)),
                        el(TextControl, {
                            label: __('', CMG_NAME),
                            className: 'cmg_select_file_txt_control',
                            placeholder: 'Download Content',
                            value: attributes.resourceURL,
                            onChange: function (newResourceURL) {
                                props.setAttributes({resourceURL: newResourceURL})
                            }
                        }),
                        el(MediaUpload, {
                            onSelect: onSelectDownloadFile,
                            //type: 'image',
                            value: attributes.mediaID,
                            render: function (obj) {
                                return el(components.Button, {
                                        className: 'cmg_select_file_btn button button-large',
                                        onClick: obj.open
                                    },
                                    el('svg', {className: 'dashicon dashicons-edit', width: '20', height: '20'},
                                        el('path', {d: 'M4.317,16.411c-1.423-1.423-1.423-3.737,0-5.16l8.075-7.984c0.994-0.996,2.613-0.996,3.611,0.001C17,4.264,17,5.884,16.004,6.88l-8.075,7.984c-0.568,0.568-1.493,0.569-2.063-0.001c-0.569-0.569-0.569-1.495,0-2.064L9.93,8.828c0.145-0.141,0.376-0.139,0.517,0.005c0.141,0.144,0.139,0.375-0.006,0.516l-4.062,3.968c-0.282,0.282-0.282,0.745,0.003,1.03c0.285,0.284,0.747,0.284,1.032,0l8.074-7.985c0.711-0.71,0.711-1.868-0.002-2.579c-0.711-0.712-1.867-0.712-2.58,0l-8.074,7.984c-1.137,1.137-1.137,2.988,0.001,4.127c1.14,1.14,2.989,1.14,4.129,0l6.989-6.896c0.143-0.142,0.375-0.14,0.516,0.003c0.143,0.143,0.141,0.374-0.002,0.516l-6.988,6.895C8.054,17.836,5.743,17.836,4.317,16.411'})
                                    ),
                                    !attributes.resourceURL ? __('Upload File', CMG_NAME) : __('Change File', CMG_NAME)
                                )
                            }
                        })
                    ),
                    el(PanelColorSettings, {
                            title: __('Color Settings', CMG_NAME),
                            className: 'block_cmg_color_settings cmg_block_panel',
                            initialOpen: true,
                            colorSettings: [
                                {
                                    label: __('Background Color', CMG_NAME),
                                    colors: [
                                        {name: 'rose', color: '#E33D48'},
                                        {name: 'red', color: '#B5343E'},
                                        {name: 'blue', color: '#00C6C2'},
                                        {name: 'green', color: '#02908D'},
                                        {name: 'yellow', color: '#FDEA59'},
                                        {name: 'silver', color: '#D0D0D0'},
                                    ],
                                    value: attributes.bgColor,
                                    onChange: function (newBgColor) {
                                        props.setAttributes({bgColor: newBgColor})
                                    }
                                },
                                {

                                    label: __('Title Color', CMG_NAME), colors: [
                                        {name: 'rose', color: '#E33D48'},
                                        {name: 'red', color: '#B5343E'},
                                        {name: 'blue', color: '#00C6C2'},
                                        {name: 'green', color: '#02908D'},
                                        {name: 'yellow', color: '#FDEA59'},
                                        {name: 'silver', color: '#D0D0D0'},
                                    ],
                                    value: attributes.titleColor,
                                    onChange: function (newTitleColor) {
                                        props.setAttributes({titleColor: newTitleColor})
                                    }
                                },
                                {
                                    label: __('Text Color', CMG_NAME), colors: [
                                        {name: 'rose', color: '#E33D48'},
                                        {name: 'red', color: '#B5343E'},
                                        {name: 'blue', color: '#00C6C2'},
                                        {name: 'green', color: '#02908D'},
                                        {name: 'yellow', color: '#FDEA59'},
                                        {name: 'silver', color: '#D0D0D0'},
                                    ],
                                    value: attributes.txtColor,
                                    onChange: function (newTxtColor) {
                                        props.setAttributes({txtColor: newTxtColor})
                                    }
                                },
                                {
                                    label: __('Button Color', CMG_NAME), colors: [
                                        {name: 'green', color: '#01CDCA'},
                                        {name: 'red', color: '#E84754'},
                                        {name: 'black', color: '#000000'},
                                        {name: 'yellow', color: '#FFED66'},
                                        {name: 'silver', color: '#D7D7D7'},
                                    ],
                                    value: attributes.btnColor,
                                    onChange: function (newBtnColor) {
                                        props.setAttributes({btnColor: newBtnColor})
                                    }
                                },
                                {
                                    label: __('Button Color - Hover', CMG_NAME), colors: [
                                        {name: 'green', color: '#01CDCA'},
                                        {name: 'red', color: '#E84754'},
                                        {name: 'black', color: '#000000'},
                                        {name: 'yellow', color: '#FFED66'},
                                        {name: 'silver', color: '#D7D7D7'},
                                    ],
                                    value: attributes.btnColorHover,
                                    onChange: function (newBtnColor) {
                                        props.setAttributes({btnColorHover: newBtnColor})
                                    }
                                },
                                {
                                    label: __('Button Text Color', CMG_NAME), colors: [
                                        {name: 'green', color: '#01CDCA'},
                                        {name: 'red', color: '#E84754'},
                                        {name: 'black', color: '#000000'},
                                        {name: 'yellow', color: '#FFED66'},
                                        {name: 'silver', color: '#D7D7D7'},
                                        {name: 'white', color: '#FFFFFF'},
                                    ],
                                    value: attributes.btnTxtColor,
                                    onChange: function (newBtnTxtColor) {
                                        props.setAttributes({btnTxtColor: newBtnTxtColor})
                                    }
                                },
                                {
                                    label: __('Button Text Color - Hover', CMG_NAME), colors: [
                                        {name: 'green', color: '#01CDCA'},
                                        {name: 'red', color: '#E84754'},
                                        {name: 'black', color: '#000000'},
                                        {name: 'yellow', color: '#FFED66'},
                                        {name: 'silver', color: '#D7D7D7'},
                                    ],
                                    value: attributes.btnTxtColorHover,
                                    onChange: function (newBtnTxtColor) {
                                        props.setAttributes({btnTxtColorHover: newBtnTxtColor})
                                    }
                                },
                                {
                                    label: __('Success Message Background Color', CMG_NAME), colors: [
                                        {name: 'green', color: '#01CDCA'},
                                        {name: 'red', color: '#E84754'},
                                        {name: 'black', color: '#000000'},
                                        {name: 'yellow', color: '#FFED66'},
                                        {name: 'silver', color: '#D7D7D7'},
                                    ],
                                    value: attributes.SMbgColor,
                                    onChange: function (newSMbgColor) {
                                        props.setAttributes({SMbgColor: newSMbgColor})
                                    }
                                },
                            ]
                        }
                    )
                ),
                el('div', {className: props.className+' '+additionalClassName, style: {background: attributes.bgColor}},
                    el('div', {className: 'cmg_form_block_inner i_cmg_item_inner'},
                        el('div', {className: 'cmg_form_block_inner_inner'},
                            el('div', {className: 'cmg_loading_div cmg_hidden'},
                                el(Spinner)
                            ),
                            attributes.magnetImageEnable && el('div', {className: 'cmg_form_left_side cmg-col-4'},
                            el('div', {
                                    className: attributes.mediaID ? 'content_magnet_image image-active' : 'content_magnet_image image-inactive',
                                    style: attributes.mediaID ? {backgroundImage: 'url(' + attributes.mediaURL + ')'} : {}
                                },
                                el(MediaUpload, {
                                    onSelect: onSelectImage,
                                    type: 'image',
                                    value: attributes.mediaID,
                                    render: function (obj) {
                                        return el(components.Button, {
                                                className: attributes.mediaID ? 'image-button' : 'button button-large cmg_uploader_preview_image',
                                                onClick: obj.open
                                            },
                                            !attributes.mediaID ?
                                                // Add Dashicon for media upload button.
                                                el('svg', {
                                                        className: 'cmg_uploader_preview_svg',
                                                        width: '20',
                                                        height: '20'
                                                    },
                                                    el('path', {d: 'M2.25 1h15.5c.69 0 1.25.56 1.25 1.25v15.5c0 .69-.56 1.25-1.25 1.25H2.25C1.56 19 1 18.44 1 17.75V2.25C1 1.56 1.56 1 2.25 1zM17 17V3H3v14h14zM10 6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zm3 5s0-6 3-6v10c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V8c2 0 3 4 3 4s1-3 3-3 3 2 3 2z'})
                                                ) : el('img', {src: attributes.mediaURL})
                                        )
                                    }
                                })
                            )
                            ),
                            el('div', {className: (attributes.magnetImageEnable) ? 'cmg_form_right_side cmg-col-8' : 'cmg_form_full_side'},
                                el('div', {className: 'content_magnet_content'}, // style: {textAlign: alignment}},
                                    el(RichText, {
                                        style: {color: attributes.titleColor},
                                        key: 'editable',
                                        tagName: 'h3',
                                        placeholder: 'Title',
                                        keepPlaceholderOnFocus: true,
                                        value: attributes.title,
                                        onChange: letCheckMagnet
                                    }),
                                    el(RichText, {
                                        style: {color: attributes.txtColor},
                                        tagName: 'p',
                                        className: 'cmg_subtitle',
                                        placeholder: __('Subtitle', CMG_NAME),
                                        keepPlaceholderOnFocus: true,
                                        value: attributes.subtitle,
                                        onChange: function (newSubtitle) {
                                            props.setAttributes({subtitle: newSubtitle})
                                        }
                                    }),
                                    el('div', {className: 'content_magnet_form_div'},
                                        el('form', {
                                                className: 'cmg_form_signup single_cmg_form',
                                                'data-magnet_id': attributes.cmg_id
                                            },
                                            el('input', {
                                                className: 'cmg_email_field',
                                                key: 1,
                                                name: 'cmg_email',
                                                placeholder: __('Email address', CMG_NAME),
                                                keepPlaceholderOnFocus: true,
                                                value: '',
                                                disabled: 'disabled'
                                            }),
                                            el('button', {
                                                    className: 'cmg_submit_btn',
                                                    style: {
                                                        'background-color': attributes.btnColor,
                                                        'color': attributes.btnTxtColor
                                                    },
                                                    type: 'submit',
                                                    disabled: 'disabled'
                                                },
                                                attributes.buttonTxt && el('span', {},
                                                attributes.buttonTxt), //Submit
                                                !attributes.buttonTxt && el('i', {
                                                    className: 'fa fa-cloud-download',
                                                    'aria-hidden': 'true'
                                                })
                                            ),
                                            attributes.redirectUrlEnable && attributes.redirectURL && el('input', {
                                                className: 'cmg_redirectURL',
                                                type: 'hidden',
                                                name: 'cmg_redirectURL',
                                                value: attributes.redirectURL
                                            }),
                                            el('input', {
                                                className: 'cmg_downloadImmediately',
                                                type: 'hidden',
                                                name: 'cmg_downloadImmediately',
                                                value: attributes.downloadImmediately
                                            })
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ]
        },

        save: function (props) {
            var attributes = props.attributes;
            var imageClass = 'wp-image-' + props.attributes.mediaID;
            var additionalClassName = 'cmg-col-10';

            resolve_in_single_magnet();
            return (
                el('div', {className: props.className+' '+additionalClassName, style: {background: attributes.bgColor}},
                    el('div', {className: 'cmg_form_block_inner i_cmg_item_inner'},
                        el('div', {className: 'cmg_form_block_inner_inner'},
                            attributes.magnetImageEnable && el('div', {className: 'cmg_form_left_side cmg-col-4'},
                            attributes.mediaURL && el('div', {
                                className: 'content_magnet_image',
                                style: {backgroundImage: 'url(' + attributes.mediaURL + ')'}
                            },
                            el('figure', {class: imageClass},
                                el('img', {src: attributes.mediaURL, alt: __('Profile Image', CMG_NAME)})
                            )
                            ),
                            ),
                            el('div', {className: (attributes.magnetImageEnable) ? 'cmg_form_right_side cmg-col-8' : 'cmg_form_full_side'},
                                el('div', {className: 'content_magnet_content'}, //style: {textAlign: alignment}},
                                    attributes.title && el(RichText.Content, {
                                        tagName: 'h3',
                                        style: {color: attributes.titleColor},
                                        value: attributes.title
                                    }),
                                    attributes.subtitle && el(RichText.Content, {
                                        tagName: 'p',
                                        className: 'cmg_subtitle',
                                        style: {color: attributes.txtColor},
                                        value: attributes.subtitle
                                    }),
                                    el('div', {className: 'content_magnet_form_div'},
                                        el('form', {
                                                className: 'cmg_form_signup single_cmg_form',
                                                'data-magnet_id': attributes.cmg_id
                                            },
                                            el('input', {
                                                className: 'cmg_email_field',
                                                //key: 1,
                                                name: 'cmg_email',
                                                placeholder: __('Email address', CMG_NAME),
                                                //keepPlaceholderOnFocus: true,
                                                value: '',
                                            }),
                                            el('button', {
                                                    className: 'cmg_submit_btn',
                                                    style: {
                                                        'background-color': attributes.btnColor,
                                                        'color': attributes.btnTxtColor
                                                    },
                                                    type: 'submit'
                                                },
                                                attributes.buttonTxt && el('span', {},
                                                attributes.buttonTxt), //Submit
                                                !attributes.buttonTxt && el('i', {
                                                    className: 'fa fa-cloud-download',
                                                    'aria-hidden': 'true'
                                                })
                                            ),
                                            el('div', {
                                                    className: 'cmg_style_generator',
                                                    style: {display: 'none'}
                                                }, //
                                                'body .cmg_submit_btn:hover{background: '+attributes.btnColorHover+'!important; color: '+attributes.btnTxtColorHover+'!important;}',
                                                'body .cmg_response.cmg_resp_success { background-color: '+attributes.SMbgColor+'; }'
                                            ),
                                            attributes.redirectUrlEnable && attributes.redirectURL && el('input', {
                                                className: 'cmg_redirectURL',
                                                type: 'hidden',
                                                name: 'cmg_redirectURL',
                                                value: attributes.redirectURL
                                            }),
                                            el('input', {
                                                className: 'cmg_downloadImmediately',
                                                type: 'hidden',
                                                name: 'cmg_downloadImmediately',
                                                value: attributes.downloadImmediately
                                            })
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        }
    });
})(
    window.wp.blocks,
    window.wp.editor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element,
    window.wp.hooks
)