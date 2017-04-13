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
        // ...
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

        // Nothing to do...
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
