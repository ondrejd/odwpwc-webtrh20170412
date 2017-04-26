jQuery( document ).ready( function() {
    // Pick license file max size
    jQuery( ".btn-pick_file_size" ).click( function( e ) {
        e.preventDefault();
        jQuery( "#max_license_size" ).val( jQuery( this ).data( "size" ) );
    } );

    // Pick allowed extensions for license file
    jQuery( ".btn-pick_file_ext" ).click( function( e ) {
        e.preventDefault();
        var ext  = jQuery( this ).data( "ext" );

        if ( ext == "*" ) {
            jQuery( "#license_allowed_exts" ).val( "jpg,gif,png,bmp,webp" );
            updateAllowedExtsBtns();
            return;
        }

        var vals = jQuery( "#license_allowed_exts" ).val().split( "," );
        var idx  = vals.indexOf( ext );

        if( idx >= 0 ) {
            vals.splice( idx, 1 );
        } else {
            vals.push( ext );
        }

        jQuery( "#license_allowed_exts" ).val( vals.join( "," ) );
        updateAllowedExtsBtns();
    } );

    /**
     * @param {Array} vals
     * @param {DOMElement} itm
     * @returns {void}
     */
    function updateBtnCls( vals, itm ) {
        var idx = vals.indexOf( jQuery( itm ).data( "ext" ) );
        if( idx >= 0 ) {
            jQuery( itm ).addClass( "hover" );
        } else {
            jQuery( itm ).removeClass( "hover" );
        }
    }

    /**
     * @returns {void}
     */
    function updateAllowedExtsBtns() {
        var vals = jQuery( "#license_allowed_exts" ).val().split( "," );
        jQuery( ".btn-pick_file_ext" ).each( function( idx, itm ) {
            updateBtnCls( vals, itm );
        } );
    }

    // At the start update buttons classes
    updateAllowedExtsBtns();
} );