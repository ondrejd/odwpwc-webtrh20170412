<?php
/**
 * Plugin Name: Úpravy pro Estets.cz
 * Plugin URI: https://github.com/ondrejd/odwpwc-webtrh20170412
 * Description: Úpravy registračního formuláře WooCommerce pro <a href="http://estets.cz/" target="blank">estets.cz</a>.
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI:
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.7.3
 *
 * Text Domain: odwpwc-webtrh20170412
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwpwc-webtrh20170412 for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwpwc-webtrh20170412
 *
 * @todo Finish customizations for WooCommerce registration form.
 * @todo Enable uploading files.
 * @todo Send registration email also to selected administrators.
 * @todo If license is attached to the user than it should be send to the administrator in the email.
 * @todo Add user preferences.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class odwpcp_webtrh20170412 {
    const SLUG = 'odwpwc-webtrh20170412';
    const FILE = __FILE__;
    const VERSION = '1.0.0';

    /**
     * Activates the plugin.
     * @internal
     * @return void
     * @since 0.0.1
     */
    public static function activate() {
        // Nothing to do...
    }

    /**
     * Deactivates the plugin directly by updating WP option `active_plugins`.
     * @internal
     * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
     * @return void
     * @since 0.0.1
     * @todo Check if using `deactivate_plugins` whouldn't be better.
     */
    public static function deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = array();
        foreach( $active_plugins as $key => $val ) {
            if( $val != sprintf( "%$1s/%$1s.php", self::SLUG ) ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }

    /**
     * Initializes the plugin.
     * @return void
     * @since 0.0.1
     */
    public static function init() {
        register_activation_hook( self::FILE, array( __CLASS__, 'activate' ) );
        register_uninstall_hook( self::FILE, array( __CLASS__, 'uninstall' ) );

        add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );
        add_action( 'plugins_loaded', array( __CLASS__, 'load_plugin' ) );
    }

    /**
     * Initialize localization (attached to "init" action).
     * @return void
     * @since 0.0.1
     * @uses load_plugin_textdomain()
     */
    public static function load_plugin_textdomain() {
        $path = dirname( __FILE__ ) . '/languages';
        load_plugin_textdomain( self::SLUG, false, $path );
    }

    /**
     * Loads plugin (attached to "plugins_loaded" action).
     * @return void
     * @since 0.0.1
     */
    public static function load_plugin() {
        add_action( 'woocommerce_register_form_start', array( __CLASS__, 'wc_register_form_start' ) );
        add_action( 'woocommerce_register_form', array( __CLASS__, 'wc_register_form' ) );
        add_action( 'woocommerce_register_form_end', array( __CLASS__, 'wc_register_form_end' ) );
        add_action( 'woocommerce_register_post', array( __CLASS__, 'wc_validate_register_form' ), 10, 3 );
    }

    /**
     * Customize WooCommerce registration form from the begining.
     */
    public static function wc_register_form_start() {
?>
<p class="woocomerce-form-row woocomerce-form-row--wide form-row form-row-wide">
    <label for="reg_user_role"><?php _e( 'Uživatelská role', self::SLUG ) ?><span class="required">*</span></label>
    <select name="user_role" id="reg_user_role">
        <option value="zakaznik" selected><?php _e( 'Zákazník', self::SLUG ) ?></option>
        <option value="kosmetolog"><?php _e( 'Kosmetolog', self::SLUG ) ?></option>
        <option value="lekar"><?php _e( 'Lékař', self::SLUG ) ?></option>
    </select>
</p>
<p class="woocomerce-form-row form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e( 'Křestní jméno', self::SLUG ) ?><span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="">
</p>
<p class="woocomerce-form-row form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e( 'Příjmení', self::SLUG ) ?><span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="">
</p>
<div class="clear"></div>
<?php
    }

    /**
     * Customize WooCommerce registration form.
     */
    public static function wc_register_form() {
?>
<p id="reg_license_row" class="woocomerce-form-row woocomerce-form-row--wide form-row form-row-wide">
    <label for="reg_license">
        <span><?php _e( 'Licence', self::SLUG ) ?><span class="required">*</span></span>
        <input type="file" id="reg_license" name="license">
    </label>
</p>
<noscript><p class="woocomerce-form-row woocomerce-form-row--wide form-row form-row-wide description">
    <?php _e( 'Licenci nahrávejte povinně jen v případě, že jste zvolili uživatelskou roli <strong>kosmetolog</strong> nebo <strong>lékař</strong>.', self::SLUG ) ?>
</p></noscript>
<?php
    }

    /**comerce-form-row--wide form-row form-row-wide description">
    <?php _e( 'Licenci nahrávejte povinně jen v p
     * Customize WooCommerce registration form at the end.
     */
    public static function wc_register_form_end() {
?>
<script type="text/javascript">
jQuery( document ).ready( function() {
    function toggle_license() {
        if ( jQuery( "#reg_user_role" ).val() != "zakaznik" ) {
            jQuery( "#reg_license_row" ).show();
        } else {
            jQuery( "#reg_license_row" ).hide();
        }
    }

    jQuery( "#reg_user_role" ).change( toggle_license );
    toggle_license();
});
</script>
<?php
    }

    /**
     * Validate our customizations of WooCommerce registration form.
     * @param string $username
     * @param string $email
     * @param WP_Error $errors
     * @return WP_Error Validation errors.
     */
    public static function wc_validate_register_form( $username, $email, WP_Error $errors ) {
        $first_name = filter_input( INPUT_POST, 'billing_first_name' );
        $last_name  = filter_input( INPUT_POST, 'billing_last_name' );

        if ( empty( $first_name ) ) {
        //if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
            $errors->add( 'billing_first_name_error', __( '<strong>Chyba</strong>: Křestní jméno je povinné!', self::SLUG ) );
        }

        if ( empty( $last_name ) ) {
        //if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
            $errors->add( 'billing_last_name_error', __( '<strong>Chyba</strong>: Příjmení je povinné!.', self::SLUG ) );
        }

        // TODO Save user role for new user.
        // TODO Check if "license" is uploaded and it is JPG (max. 5MB).

        return $errors;
    }

    /**
     * Uninstalls the plugin.
     * @internal
     * @return void
     * @since 0.0.1
     */
    private static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...<?php _e<?php _e
    }

    /**
     * Check requirements of the plugin.
     * @internal
     * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
     * @return boolean Returns TRUE if requirements are met.
     * @since 0.0.1
     * @todo Current solution doesn't work for WPMU...
     */
    public static function requirements_check() {
        $active_plugins = (array) get_option( 'active_plugins', array() );
        return in_array( 'woocommerce/woocommerce.php', $active_plugins ) ? true : false;
    }

    /**
     * Shows error in WP administration that minimum requirements were not met.
     * @internal
     * @return void
     * @since 0.0.1
     */
    public static function requirements_error() {
        self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> vyžaduje, aby byl nejprve nainstalovaný a aktivovaný plugin <b>WooCommerce</b>.', 'odwpwcgp' ), 'error' );
        self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> byl <b>deaktivován</b>.', 'odwpwcgp' ), 'updated' );
    }

    /**
     * Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @return void
     * @since 0.0.1
     */
    private static function print_error( $msg, $type = 'info' ) {
        $avail_types = array( 'error', 'info', 'updated' );
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }
} // End of odwpcp_webtrh20170412


// Our plug-in is dependant on WooCommerce
if( !odwpcp_webtrh20170412::requirements_check() ) {
    odwpcp_webtrh20170412::deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', array( odwpcp_webtrh20170412, 'requirements_error ') );
    }
} else {
    // WooCommerce is present so initialize our plugin
    odwpcp_webtrh20170412::init();
}
