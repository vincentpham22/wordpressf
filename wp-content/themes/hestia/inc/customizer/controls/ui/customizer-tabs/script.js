/**
 * Script for the customizer tabs control interactions.
 *
 * @since    1.1.43
 * @package Hestia
 *
 * @author    ThemeIsle
 */

/* global wp */


wp.customize.controlConstructor['interface-tabs'] = wp.customize.Control.extend({
	ready: function() {

        // Switch tab based on customizer partial edit links.
        wp.customize.previewer.bind(
            'tab-previewer-edit', function( data ) {
                jQuery( data.selector ).trigger( 'click' );
            }
        );

        wp.customize.previewer.bind(
            'focus-control',  function( data ) {
                /**
                 * This timeout is here because in firefox this happens before customizer animation of changing panels.
                 * After it change panels with the input focused, the customizer was moved to right 12px. We have to make sure
                 * that the customizer animation of changing panels in customizer is done before focusing the input.
                 */
                setTimeout( function(){
                    var control = wp.customize.control(data);
                    if( typeof control !== 'undefined'){
                        wp.customize.control(data).focus();
                    }
                } , 100 );
            }
        );

        wp.customize.previewer.bind(
			'focus-section',  function( data ) {
					/**
					  * This timeout is here because in firefox this happens before customizer animation of changing panels.
					  * After it change panels with the input focused, the customizer was moved to right 12px. We have to make sure
					  * that the customizer animation of changing panels in customizer is done before focusing the input.
					  */
						setTimeout( function(){
								wp.customize.section(data).focus();
							} , 100 );
				}
		);

        wp.customize.previewer.bind( 'ready', function () {
			var parts = window.location.search.substr(1).split('&');
			var $_GET = {};
			for (var i = 0; i < parts.length; i++) {
				var temp = parts[i].split('=');
				$_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
			}




			if( typeof $_GET['autofocus[control]'] !== 'undefined' && $_GET['autofocus[control]'] !== ''){
				jQuery('li[id^="customize-control-sidebars_widgets"]').on(
					'DOMNodeInserted', function () {
						jQuery('.hestia-customizer-tab > label.' + $_GET['autofocus[control]']).trigger('click');
				});
			}
        } );

        this.init();
		this.handleClick();
    },

	init: function () {
		var control = this;
		var section = control.section();

        /**
         * This timeout is required because widget controls are rendered async and need some time.
         */
        setTimeout( function() {
            jQuery('li[id^="customize-control-hestia_"]').each(function () {
                jQuery(this).on(
                    'DOMNodeInserted', function () {
                        jQuery('.hestia-customizer-tab.active > label').trigger('click');
                    });
            });
        }, 100 );

        wp.customize.bind('ready',function () {
            control.hideAllControls(section);
            var tab = Object.keys(control.params.controls)[0];
            var controlsToShow = control.params.controls[tab];
            var allControls = [];
            for (var controlName in controlsToShow ){
				if( controlsToShow.hasOwnProperty(controlName)) {
                    if (jQuery.isEmptyObject(controlsToShow[controlName]) === false &&
                        typeof wp.customize.control(controlName) !== 'undefined') {
                        var subTabValue = wp.customize.control(controlName).setting._value;
                        allControls = allControls.concat(controlsToShow[controlName][subTabValue]);
                    }
                    allControls.push(controlName);
                }
            }
            control.showControls(allControls, section);
        });

    },

	hideAllControls: function ( section ) {
        var controls = wp.customize.section(section).controls();
        var tabControl = this.id;
        var excludeControls = [ 'hestia_pricing_upsell_notice' ];
        for( var i in controls ){
			var controlId = controls[i].id;
            if ( jQuery.inArray( controlId, excludeControls ) !== -1 ) {
                var selector = wp.customize.control(controlId).selector;
                var controlFields = jQuery( selector + '~ li:visible' );
                if ( controlFields.length > 0 ) {
                    var controlFieldsLabel = controlFields.find( 'label, span.customize-control-title' );
                    var hasLockedIcon = controlFieldsLabel.find( '.dashicons-lock' );
                    if ( hasLockedIcon.length === 0 ) {    
                        controlFieldsLabel.html( controlFieldsLabel.html() + ' <span class="dashicons dashicons-lock"></span>' );
                    }
                }
            }
            if ( jQuery.inArray( controlId, excludeControls ) === -1 ) {
    			if( controlId === 'widgets' ){
                    var sectionContainer = wp.customize.section(section).container;
                    jQuery( sectionContainer ).children( 'li[class*="widget"]' ).css( 'display', 'none' );
    			} else {
    				if( controlId !== tabControl ){
    					var selector = wp.customize.control(controlId).selector;
    					jQuery(selector).hide();
    				}
    			}
            }
		}
    },

    handleClick: function () {
		var control = this;
        var section = control.section();
        var container = control.container;
		jQuery(container).find('.hestia-customizer-tab').on( 'click', function () {
            jQuery( this ).parent().find('.hestia-customizer-tab').removeClass('active');
            jQuery( this ).addClass('active');
			control.hideAllControls(section);
			var tab = jQuery(this).data('tab');
			var controlsToShow = control.params.controls[tab];
			var allControls = [];
			for (var controlName in controlsToShow ){
				if( jQuery.isEmptyObject(controlsToShow[controlName]) === false &&
				    typeof wp.customize.control(controlName) !== 'undefined' ){
					var subTabValue = wp.customize.control(controlName).setting._value;
					allControls = allControls.concat(controlsToShow[controlName][subTabValue]);
				}
				allControls.push(controlName);
			}
			control.showControls(allControls, section);
        } );
    },

    showControls: function (controls, section) {
		for(var i in controls ){
			var controlName = controls[i];
			if( controlName === 'widgets' ) {
                var sectionContainer = wp.customize.section(section).container;
                jQuery( sectionContainer ).children( 'li[class*="widget"]' ).css( 'display', 'list-item' );
            } else {
				if( typeof wp.customize.control(controlName) !== 'undefined' ) {
                    var selector = wp.customize.control(controlName).selector;
                    jQuery(selector).show();
                }
			}
		}
    }
});

wp.customize.control( 'hestia_slider_type', function( control ) {
    var sectionGroup = jQuery( control.container ).parents( 'ul' );
    var selectedType = control.setting._value || '';
    if ( 'video' === selectedType ) {
        sectionGroup.addClass( 'hestia-locked-icon' );
    }
    var sectionControls = sectionGroup.find( 'li:is(#customize-control-header_video,#customize-control-external_header_video,#customize-control-hestia_slider_content,#customize-control-hestia_slider_alignment),#customize-control-hestia_slider_disable_autoplay,#customize-control-hestia_slider_tabs' );
    jQuery.each( sectionControls, function() {
        var controlFieldsLabel = jQuery( this ).find( 'label:not(.ui-checkboxradio-radio-label), span.customize-control-title' );
        jQuery.each( controlFieldsLabel, function() {
            var hasLockedIcon = jQuery( this ).find( '.dashicons-lock' );
            if ( hasLockedIcon.length === 0 ) {
                jQuery( this ).html( jQuery( this ).html() + ' <span class="dashicons dashicons-lock"></span>' );
            }
        } );
    } );
    control.container.on( 'change', 'select', function( event ) {
        var isLocked = jQuery( 'option:selected', this ).hasClass( 'hestia-locked-icon' );
        if ( isLocked ) {
            jQuery( this )
            .parents( 'ul' )
            .addClass( 'hestia-locked-icon' );
            return;
        }
        jQuery( this )
        .parents( 'ul' )
        .removeClass( 'hestia-locked-icon' );
    } );
} );