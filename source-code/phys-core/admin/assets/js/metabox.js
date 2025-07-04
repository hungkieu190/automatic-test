(function ($) {

    const PhysCodeMetaboxTabs = () => {
        $( document.body ).on( 'physcode-metabox-tab', function() {
            $( 'ul.physcode-metabox-tab__title' ).show();

            $( 'ul.physcode-metabox-tab__title a' ).on( 'click', function( e ) {
                e.preventDefault();

                const panelWrap = $( this ).closest( 'div.physcode-metabox-tab' );

                $( 'ul.physcode-metabox-tab__title li', panelWrap ).removeClass( 'active' );

                $( this ).parent().addClass( 'active' );

                $( 'div.physcode-metabox-tab__content--inner', panelWrap ).hide();

                $( 'div.physcode-metabox-tab__content--inner[data-content="' + $( this ).data( 'content' ) + '"]', panelWrap ).show();
            } );

            $( 'div.physcode-metabox-tab' ).each( function() {
                $( this ).find( 'ul.physcode-metabox-tab__title li' ).eq( 0 ).find( 'a' ).trigger( 'click' );
            } );
        } ).trigger( 'physcode-metabox-tab' );
    };

    const physMetaboxFileInput = () => {
        $( '.phys-meta-box__file' ).each( ( i, element ) => {
            let physImageFrame;

            const imageGalleryIds = $( element ).find( '.phys-meta-box__file_input' );
            const listImages = $( element ).find( '.phys-meta-box__file_list' );
            const btnUpload = $( element ).find( '.btn-upload' );
            const isMultil = !! $( element ).data( 'multil' );

            $( btnUpload ).on( 'click', ( event ) => {
                event.preventDefault();

                if ( physImageFrame ) {
                    physImageFrame.open();
                    return;
                }

                physImageFrame = wp.media( {
                    states: [
                        new wp.media.controller.Library( {
                            filterable: 'all',
                            multiple: isMultil,
                        } ),
                    ],
                } );

                physImageFrame.on( 'select', function() {
                    const selection = physImageFrame.state().get( 'selection' );
                    let attachmentIds = imageGalleryIds.val();

                    selection.forEach( function( attachment ) {
                        attachment = attachment.toJSON();

                        if ( attachment.id ) {
                            if ( ! isMultil ) {
                                attachmentIds = attachment.id;
                                listImages.empty();
                            } else {
                                attachmentIds = attachmentIds ? attachmentIds + ',' + attachment.id : attachment.id;
                            }

                            if ( attachment.type === 'image' ) {
                                const attachmentImage = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                                listImages.append(
                                    '<li class="phys-meta-box__file_list-item image" data-attachment_id="' + attachment.id + '"><img src="' + attachmentImage +
                                    '" /><ul class="actions"><li><a href="#" class="delete"></a></li></ul></li>'
                                );
                            } else {
                                listImages.append(
                                    '<li class="phys-meta-box__file_list-item image" data-attachment_id="' + attachment.id + '"><img class="is_file" src="' + attachment.icon +
                                    '" /><span>' + attachment.filename + '</span><ul class="actions"><li><a href="#" class="delete"></a></li></ul></li>'
                                );
                            }
                        }
                    } );

                    delImage();

                    imageGalleryIds.val( attachmentIds );
                } );

                physImageFrame.open();
            } );

            if ( isMultil ) {
                listImages.sortable( {
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'phys-metabox-sortable-placeholder',
                    start( event, ui ) {
                        ui.item.css( 'background-color', '#f6f6f6' );
                    },
                    stop( event, ui ) {
                        ui.item.removeAttr( 'style' );
                    },
                    update() {
                        let attachmentIds = '';

                        listImages.find( 'li.image' ).css( 'cursor', 'default' ).each( function() {
                            const attachmentId = $( this ).attr( 'data-attachment_id' );
                            attachmentIds = attachmentIds + attachmentId + ',';
                        } );

                        delImage();

                        imageGalleryIds.val( attachmentIds );
                    },
                } );
            }

            const delImage = () => {
                $( listImages ).find( 'li.image' ).each( ( i, ele ) => {
                    const del = $( ele ).find( 'a.delete' );

                    del.on( 'click', function() {
                        $( ele ).remove();

                        if ( isMultil ) {
                            let attachmentIds = '';

                            $( listImages ).find( 'li.image' ).css( 'cursor', 'default' ).each( function() {
                                const attachmentId = $( this ).attr( 'data-attachment_id' );
                                attachmentIds = attachmentIds + attachmentId + ',';
                            } );

                            imageGalleryIds.val( attachmentIds );
                        } else {
                            imageGalleryIds.val( '' );
                        }

                        return false;
                    } );
                } );
            };

            delImage();
        } );
    };

    const physSelect2 = () => {
        if ( $.fn.select2 ) {
            $( '.phys-select-2 select' ).select2();
        }
    };

    const physMetaboxColorPicker = () => {
        $( '.phys-meta-box__color--input' ).wpColorPicker();
    };

    const physShowHide = () => {
        const metaBoxes = document.querySelectorAll( '.physcode-meta-box' );
        let postEle = document.querySelector( '.editor-post-format .components-radio-control__input' );
        let isClassicEditor = false;

        // Classic Editor.
        if ( ! postEle ) {
            postEle = document.querySelectorAll( 'input[name=post_format]' );
            isClassicEditor = true;
        }

        const toggleEle = ( postType ) => {
            [...metaBoxes].map( ele => {
                if ( ele.dataset.show ) {
                    const datas = JSON.parse( ele.dataset.show );

                    if ( postType && datas.post_format ) {
                        if ( (datas.post_format).includes( postType.value ) ) {
                            ele.parentNode.parentNode.style.display = "block";
                        } else {
                            ele.parentNode.parentNode.style.display = "none";
                        }
                    }
                }
            });
        }


        // Classic Editor.
        if ( isClassicEditor && [...postEle].length > 0 ) {
            [...postEle].map( postType => {
                if ( postType.checked ) {
                    toggleEle( postType );
                }

                postType.addEventListener('change', () => {
                    toggleEle( postType )
                } );
            });
        } else if ( postEle.length > 0  ) {
            toggleEle( postEle );

            postEle.addEventListener('change', () => {
                toggleEle( postEle )
            } );
        }
    }

    const physCondition = () => {
        const metaBoxes = document.querySelectorAll( '.physcode-meta-box' );

        [...metaBoxes].map( ele => {
            const formField = ele.querySelectorAll( '.form-field' );

            [...formField].map( field => {
                if ( field.hasAttribute('data-hide') && field.dataset.hide ) {
                    let dataHide = JSON.parse( field.dataset.hide );

                    let eleHides = ele.querySelectorAll( `input[id^="${dataHide[0]}"]` );

                    [...eleHides].map( eleHide => {
                        let type = eleHide.getAttribute( 'type' );

                        if ( eleHide ) {
                            switch( type ) {
                                case 'checkbox':
                                    if ( dataHide[1] == '!=' && dataHide[2] !== Boolean( eleHide.checked ) ) {
                                        field.style.display = 'none';
                                    } else if ( dataHide[1] == '=' && dataHide[2] == Boolean( eleHide.checked ) ) {
                                        field.style.display = 'none';
                                    } else {
                                        if ( field.classList.contains('physcode-meta-box-group') ) {
                                            field.style.display = 'block';
                                        } else {
                                            field.style.display = 'flex';
                                        }
                                    }
                                    break;

                                case 'radio':
                                    if ( eleHide.checked ) {
                                        if ( dataHide[1] == '!=' && dataHide[2] !== eleHide.value ) {
                                            field.style.display = 'none';
                                        } else if ( dataHide[1] == '=' && dataHide[2] == eleHide.value ) {
                                            field.style.display = 'none';
                                        } else {
                                            if ( field.classList.contains('physcode-meta-box-group') ) {
                                                field.style.display = 'block';
                                            } else {
                                                field.style.display = 'flex';
                                            }
                                        }
                                    }
                                    break;
                            }

                            eleHide.addEventListener( 'change', (e) => {
                                let target = e.target;

                                switch( type ) {
                                    case 'checkbox':
                                        if ( dataHide[1] == '!=' && dataHide[2] !== Boolean( target.checked ) ) {
                                            field.style.display = 'none';
                                        } else if ( dataHide[1] == '=' && dataHide[2] == Boolean( target.checked ) ) {
                                            field.style.display = 'none';
                                        } else {
                                            if ( field.classList.contains('physcode-meta-box-group') ) {
                                                field.style.display = 'block';
                                            } else {
                                                field.style.display = 'flex';
                                            }
                                        }
                                        break;

                                    case 'select':
                                    case 'radio':
                                        if ( dataHide[1] == '!=' && dataHide[2] !== target.value ) {
                                            field.style.display = 'none';
                                        } else if ( dataHide[1] == '=' && dataHide[2] == target.value ) {
                                            field.style.display = 'none';
                                        } else {
                                            if ( field.classList.contains('physcode-meta-box-group') ) {
                                                field.style.display = 'block';
                                            } else {
                                                field.style.display = 'flex';
                                            }
                                        }
                                        break;
                                }
                            } );
                        }
                    } );
                }
            });
        });
    }

    $(document).ready( function() {
        // PhysCodeMetaboxTabs();
        physMetaboxFileInput();
        physSelect2();
        physMetaboxColorPicker();
        physCondition();
        setTimeout( function() {
            physShowHide();
        }, 1000 );
    } );
})(jQuery);

jQuery( function ( $ ) {
    'use strict';

    const { select } = wp.data;

    // Global variables
    var $parent = $( '#parent_id' );

    // List of selectors for each type of element.
    // Made for compatibility with classic and Gutenberg editors.
    var selectors = {
        'template': ['#page_template', '.edit-post-post-template__dialog .components-select-control__input'],
        'post_format': ['input[name="post_format"]', '.editor-post-format .components-radio-control__input'],
        'parent': ['#parent_id'], // No selector in Gutenberg, thus not working.
    };
    var elements = {};

    var $document = $( document );

    var initialCheck = false;

    function initElements() {
        _.forEach( selectors, function( list, key ) {
            var selector = _.find( list, function( selector ) {
                return $( selector ).length;
            } );
            elements[key] = $( selector );
        } );
    }

    // Callback functions to check for each condition
    var checkCallbacks = {
        template   : function ( templates ) {
            var value = initialCheck ? elements.template.val() : MBShowHideData.template,
                result = -1 !== templates.indexOf( value );
            console.log( 'Check by template:', result );
            return result;
        },
        post_format: function ( formats ) {
            // Make sure registered formats in lowercase
            formats = formats.map( function ( format ) {
                return format.toLowerCase();
            } );

            var value = MBShowHideData.post_format;
            if ( initialCheck ) {
                value = elements.post_format.is( 'select' ) ? elements.post_format.val() : elements.post_format.filter( ':checked' ).val();
            }
            if ( !value || 0 == value ) {
                value = 'standard';
            }

            var result = -1 != formats.indexOf( value );
            console.log( 'Check by post format:', result );
            return result;
        },
        taxonomy   : function ( taxonomy, terms ) {
            var values = [],
                $inputs = $( '#' + taxonomy + 'checklist :checked' );

            $inputs.each( function () {
                var $input = $( this ),
                    text = $.trim( $input.parent().text() );
                values.push( parseInt( $input.val() ) );
                values.push( text );
            } );

            for ( var i = 0, len = values.length; i < len; i++ ) {
                if ( -1 != terms.indexOf( values[i] ) ) {
                    return true;
                }
            }
            return false;
        },
        input_value: function ( inputValues, relation ) {
            relation = relation || 'OR';

            for ( var i in inputValues ) {
                var $element = $( i ),
                    value = $.trim( $element.val() ),
                    checked = null;

                if ( $element.is( ':checkbox' ) ) {
                    checked = $element.is( ':checked' ) === !!inputValues[i];
                }

                if ( $element.is( ':radio' ) ) {
                    value = $.trim( $element.filter( ':checked' ).val() );
                }

                if ( 'OR' == relation ) {
                    if ( ( value == inputValues[i] && checked === null ) || checked === true ) {
                        return true;
                    }
                } else {
                    if ( ( value != inputValues[i] && checked === null ) || checked === false ) {
                        return false;
                    }
                }
            }
            return relation != 'OR';
        },
        is_child   : function () {
            var value = initialCheck ? elements.parent.val() : MBShowHideData.parent,
                result = !! parseInt( value );
            console.log( 'Check by is child:', result );
            return result;
        }
    };

    // Callback functions to addEventListeners for "change" event in each condition
    var addEventListenersCallbacks = {
        /**
         * Check by page templates
         *
         * @param callback Callback function
         *
         * @return bool
         */
        template   : function ( callback ) {
            $document.on( 'change', selectors.template.join( ',' ), callback );
        },
        post_format: function ( callback ) {
            $document.on( 'change', selectors.post_format.join( ',' ), callback );
        },
        taxonomy   : function ( taxonomy, callback ) {
            // Fire "change" event when click on popular category
            // See wp-admin/js/post.js
            $( '#' + taxonomy + 'checklist-pop' ).on( 'click', 'input', function () {
                var t = $( this ), val = t.val(), id = t.attr( 'id' );
                if ( !val ) {
                    return;
                }

                var tax = id.replace( 'in-popular-', '' ).replace( '-' + val, '' );
                $( '#in-' + tax + '-' + val ).trigger( 'change' );
            } );

            $( '#' + taxonomy + 'checklist' ).on( 'change', 'input', callback );
        },
        input_value: function ( callback, selector ) {
            $( selector ).on( 'change', callback );
        },
        is_child   : function ( callback ) {
            $document.on( 'change', selectors.parent.join( ',' ), callback );
        }
    };

    /**
     * Add JS addEventListenersers to check conditions to show/hide a meta box
     * @param type
     * @param conditions
     * @param $metaBox
     */
    function maybeShowHide( type, conditions, $metaBox ) {
        var condition = checkAllConditions( conditions );

        if ( 'show' == type ) {
            condition ? $metaBox.show() : $metaBox.hide();
        } else {
            condition ? $metaBox.hide() : $metaBox.show();
        }
    }

    /**
     * Check all conditions
     * @param conditions Array of all conditions
     *
     * @return bool
     */
    function checkAllConditions( conditions ) {
        // Don't change "global" conditions
        var localConditions = $.extend( {}, conditions );

        var relation = localConditions.hasOwnProperty( 'relation' ) ? localConditions['relation'].toUpperCase() : 'OR',
            value;

        // For better loop of checking terms
        if ( localConditions.hasOwnProperty( 'relation' ) ) {
            delete localConditions['relation'];
        }

        initElements();

        var checkBy = ['template', 'post_format', 'input_value', 'is_child'],
            by, condition;

        for ( var i = 0, l = checkBy.length; i < l; i++ ) {
            by = checkBy[i];

            if ( !localConditions.hasOwnProperty( by ) ) {
                continue;
            }

            // Call callback function to check for each condition
            condition = checkCallbacks[by]( localConditions[by], relation );

            if ( 'OR' == relation ) {
                value = typeof value == 'undefined' ? condition : value || condition;
                if ( value ) {
                    break;
                }
            } else {
                value = typeof value == 'undefined' ? condition : value && condition;
                if ( !value ) {
                    break;
                }
            }

            delete localConditions[by];
        }

        // By taxonomy, including category and post format
        // Note that we unset all other parameters, so we can safely loop in the localConditions array
        if ( localConditions.length ) {
            for ( var taxonomy in localConditions ) {
                if ( !localConditions.hasOwnProperty( taxonomy ) ) {
                    continue;
                }

                // Call callback function to check for each condition
                condition = checkCallbacks['taxonomy']( taxonomy, localConditions[taxonomy] );

                if ( 'OR' == relation ) {
                    value = typeof value == 'undefined' ? condition : value || condition;
                    if ( value ) {
                        break;
                    }
                } else {
                    value = typeof value == 'undefined' ? condition : value && condition;
                    if ( !value ) {
                        break;
                    }
                }
            }
        }

        initialCheck = true;

        return value;
    }

    /**
     * Add event addEventListenersers for "change" event
     * This will re-check all conditions to show/hide meta box
     * @param type
     * @param conditions
     * @param $metaBox
     */
    function addEventListeners( type, conditions, $metaBox ) {
        // Don't change "global" conditions
        var localConditions = $.extend( {}, conditions );

        // For better loop of checking terms
        if ( localConditions.hasOwnProperty( 'relation' ) ) {
            delete localConditions['relation'];
        }

        var checkBy = ['template', 'post_format', 'input_value', 'is_child'], by;
        for ( var i = 0, l = checkBy.length; i < l; i++ ) {
            by = checkBy[i];

            if ( ! localConditions.hasOwnProperty( by ) ) {
                continue;
            }

            if ( 'input_value' != by ) {
                // Call callback function to check for each condition
                addEventListenersCallbacks[by]( function () {
                    maybeShowHide( type, conditions, $metaBox );
                } );
                delete localConditions[by];
                continue;
            }

            // Input values
            for ( var selector in localConditions[by] ) {
                // Call callback function to check for each condition
                addEventListenersCallbacks[by]( function () {
                    maybeShowHide( type, conditions, $metaBox );
                }, selector );
            }
            delete localConditions[by];

        }

        // By taxonomy, including category and post format
        // Note that we unset all other parameters, so we can safely loop in the localConditions array
        if ( !localConditions.length ) {
            for ( var taxonomy in localConditions ) {
                if ( ! localConditions.hasOwnProperty( taxonomy ) ) {
                    continue;
                }

                // Call callback function to check for each condition
                addEventListenersCallbacks['taxonomy']( taxonomy, function () {
                    maybeShowHide( type, conditions, $metaBox );
                } );
            }
        }
    }

    function init() {
        // Gutenberg get init post format
        var initPostFormat = select('core/editor').getEditedPostAttribute('format');

        $( '.physcode-meta-box' ).each( function () {
            var $this = $( this ),
                $metaBox = $this.closest( '.postbox' ),
                conditions;

            // show .postbox init
            $('.postbox-container #phys_metabox_' + initPostFormat ).show();

            // Check for show rules
            if ( $this.data( 'show' ) ) {
                conditions = $this.data( 'show' );
                maybeShowHide( 'show', conditions, $metaBox );
            }

            // Check for hide rules
            if ( $this.data( 'hide' ) ) {
                conditions = $this.data( 'hide' );
                maybeShowHide( 'hide', conditions, $metaBox );
            }
        } );
    }

    function initEventListeners() {
        $( '.physcode-meta-box' ).each( function () {
            var $this = $( this ),
                $metaBox = $this.closest( '.postbox' ),
                conditions;

            // Check for show rules
            if ( $this.data( 'show' ) ) {
                conditions = $this.data( 'show' );
                addEventListeners( 'show', conditions, $metaBox );
            }

            // Check for hide rules
            if ( $this.data( 'hide' ) ) {
                conditions = $this.data( 'hide' );
                addEventListeners( 'hide', conditions, $metaBox );
            }
        } );
    }

    // Run the code after Gutenberg has done rendering.
    // https://stackoverflow.com/a/34999925/371240
    setTimeout( function() {
        window.requestAnimationFrame( function() {
            initElements();
            init();
            initEventListeners();
        } );
    }, 1000 );
} );
