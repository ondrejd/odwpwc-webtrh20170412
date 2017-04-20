jQuery( document ).ready( function() {
    function toggle_license() {
        if ( jQuery( "#reg_user_role" ).val() != odwpwcw20170412.ROLE_CUSTOMER ) {
            jQuery( ".reg-license-row" ).show();
        } else {
            jQuery( ".reg-license-row" ).hide();
        }
    }

    jQuery( "#reg_user_role" ).change( toggle_license );
    toggle_license();

    // Pre-validate file before uploading
    jQuery( "form" ).submit( function( e ) {
        if ( jQuery( "#reg_user_role" ).val() == odwpwcw20170412.ROLE_CUSTOMER ) {
            return true;
        }

        var file = jQuery( "#reg_license" ).val();

        if ( file.empty() || ! file ) {
            alert( odwpwcw20170412.msg1 );
        }

        var ext = file.split( "." ).pop().toLowerCase();
        var file_size = jQuery( "#reg_license" )[0].files[0].size;

        if ( ! ( file_size < odwpwcw20170412.file_size && ext == odwpwcw20170412.allowed_ext ) ) {
            // Prevent default and display error
            alert( odwpwcw20170412.msg2 );
            e.preventDefault();
        }

        jQuery( "#license" ).val( file );
    } );
} );