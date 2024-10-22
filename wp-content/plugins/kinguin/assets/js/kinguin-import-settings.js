jQuery(document).ready(function($) {

    let isActivePreorder = jQuery('input:radio[id="kinguin_filter_preorder-yes"]');

    if (jQuery(isActivePreorder).is(':checked')) {
        jQuery('.hidden-activepreorder-option').show();
    }


    $('.postbox-header').click(function() {
        let postbox_frame = $(this).parent('.postbox');

        if($(postbox_frame).hasClass('closed')) {
            $(postbox_frame).removeClass('closed');
        } else {
            $(postbox_frame).addClass('closed');
        }
    });

    if ( jQuery('#kinguin_enable_webhook_import').is(':checked') ) {
        jQuery('.kinguin-webhook-additional-settings').closest('tr').show();
    } else {
        jQuery('.kinguin-webhook-additional-settings').closest('tr').hide();
    }

    jQuery('#kinguin_enable_webhook_import').on('change', function () {
        if ( jQuery(this).is(':checked') ) {
            jQuery('.kinguin-webhook-additional-settings').closest('tr').show();
        } else {
            jQuery('.kinguin-webhook-additional-settings').closest('tr').hide();
        }
    });

    jQuery('input.kinguin-price-range-qty').on('keypress', function () {
        return blockNonNumbers(this, event, true, false)
    });

    jQuery(isActivePreorder).change(
        function() {
            if (jQuery(this).is(':checked') && $(this).val() === 'yes') {
                jQuery('.hidden-activepreorder-option').show();
            }
        });


    jQuery('#choose_all_tags').change(function () {

        if(this.checked) {
            jQuery('.kinguin-tags').each(function(i,elem) {
                elem.checked = true;
            })
        } else {
            jQuery('.kinguin-tags').each(function(i,elem) {
                elem.checked = false;
            })
        }
    });


    jQuery('#choose_all_genres').change(function () {

        if(this.checked) {
            jQuery('.kinguin-genre').each(function(i,elem) {
                elem.checked = true;
            })
        } else {
            jQuery('.kinguin-genre').each(function(i,elem) {
                elem.checked = false;
            })
        }
    });


    jQuery('#choose_all_platforms').change(function () {

        if(this.checked) {
            jQuery('.kinguin-platform').each(function(i,elem) {
                elem.checked = true;
            })
        } else {
            jQuery('.kinguin-platform').each(function(i,elem) {
                elem.checked = false;
            })
        }
    });



    jQuery('input:radio[id="kinguin_filter_preorder-no"]').change(
        function() {
            if (jQuery(this).is(':checked') && $(this).val() === 'no') {
                jQuery('.hidden-activepreorder-option').hide();
                document.getElementById("kinguin_filter_active_preorder").checked = false;
            }
        });

    jQuery('#kinguin-reset-filter').click(function() {

        jQuery('.kinguin-import-filter-input').each(function(i,elem) {
            let inp_type = jQuery(elem).attr('type');
            if(inp_type === 'text') {
                jQuery(elem).val('');
            }
            if(inp_type === 'checkbox' || inp_type === 'radio' ) {
                elem.checked = false;
            }
        })
    });

    jQuery('#kinguin-reset-language').click(function(e) {
        e.preventDefault();
        jQuery('input[name="kinguin_settings_import[languages]"]').each(function(i,elem) {
            elem.checked = false;
        })
    });


    jQuery('#kinguin-reset-preorder').click(function(e) {
        e.preventDefault();
        jQuery('input[name="kinguin_settings_import[isPreorder]"]').each(function(i,elem) {
            elem.checked = false;
        });
        jQuery('#kinguin_filter_active_preorder').prop('checked', false);
    });


    jQuery('#kinguin-reset-region').click(function(e) {
        e.preventDefault();
        jQuery('input[name="kinguin_settings_import[regionId]"]').each(function(i,elem) {
            elem.checked = false;
        })
    });


    jQuery('#kinguin-reset-merchant').click(function(e) {
        e.preventDefault();
        jQuery('input[name="kinguin_settings_import[merchantName]"]').each(function(i,elem) {
            elem.checked = false;
        })
    });


});


function blockNonNumbers(e, t, n, r) {
    var i;
    var s = false;
    var o;
    var u;
    if (window.event) {
        i = t.keyCode;
        s = window.event.ctrlKey
    } else if (t.which) {
        i = t.which;
        s = t.ctrlKey
    }
    if (isNaN(i)) return true;
    o = String.fromCharCode(i);
    if (i == 8 || s) {
        return true
    }
    //u = /^-?\d+$/;
    u = /\d/;
    var a = r ? o == "-" && e.value.indexOf("-") == -1 : false;
    var f = n ? o == "." && e.value.indexOf(".") == -1 : false;
    return a || f || u.test(o)
}