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
 * @todo Enable uploading files.
 * @todo Send registration email also to selected administrators.
 * @todo If license is attached to the user than it should be send to the administrator in the email.
 * @todo Add options for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'odwpcp_webtrh20170412' ) ) :

/**
 * My plugin.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 */
class odwpcp_webtrh20170412 {
    /**
     * @const string Plugin's slug.
     */
    const SLUG = 'odwpwc-webtrh20170412';

    /**
     * @const string Plugin's version.
     */
    const VERSION = '1.0.0';

    /**
     * @const string Name of upload directory for the licenses (within wp-content).
     */
    const UPLOAD = 'licenses';

    const ROLE_CUSTOMER = 'zakaznik';
    const ROLE_COSMETOLOGIST = 'kosmetolog';
    const ROLE_PHYSICIAN = 'lekar';

    /**
     * @const array
     */
    const AVAILABLE_ROLES = [
            self::ROLE_CUSTOMER,
            self::ROLE_COSMETOLOGIST,
            self::ROLE_PHYSICIAN,
    ];

    /**
     * @const string.
     */
    const SETTINGS_KEY = 'odwpwcw_settings';

    /**
     * Activates the plugin.
     * @return void
     */
    public static function activate() {
        // Nothing to do...
    }

    /**
     * @internal Deactivates the plugin directly by updating WP option `active_plugins`.
     * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
     * @return void
     * @todo Check if using `deactivate_plugins` whouldn't be better.
     */
    public static function deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != sprintf( "%$1s/%$1s.php", self::SLUG ) ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }

    /**
     * @return array Default values for settings of the plugin.
     */
    public static function get_default_options() {
        return [
            'upload_dir'         => __( 'licence', self::SLUG ),
            'max_file_size'      => self::get_max_upload_size(),
            'allowed_extensions' => 'jpg,jpeg'
        ];
    }

    /**
     * @return array Settings of the plugin.
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option( self::SETTINGS_KEY, [] );
        $update = false;

        // Fill defaults for the options that are not set yet
        foreach( $defaults as $key => $val ) {
            if( ! array_key_exists( $key, $options ) ) {
                $options[$key] = $val;
                $update = true;
            }
        }

        // Updates options if needed
        if( $update === true) {
            update_option( self::SETTINGS_KEY, $options );
        }

        return $options;
    }

    /**
     * Returns value of option with given key.
     * @param string $key Option's key.
     * @return mixed Option's value.
     * @throws Exception Whenever option with given key doesn't exist.
     */
    public static function get_option( $key ) {
        $options = self::get_options();

        if( ! array_key_exists( $key, $options ) ) {
            throw new Exception( 'Option "'.$key.'" is not set!' );
        }

        return $options[$key];
    }

    /**
     * Initializes the plugin.
     * @return void
     */
    public static function init() {
        register_activation_hook( __FILE__, [__CLASS__, 'activate'] );
        register_uninstall_hook( __FILE__, [__CLASS__, 'uninstall'] );
        add_action( 'init', [__CLASS__, 'init_textdomain'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
    }

    /**
     * Hook for "init" action.
     * @return void
     */
    public static function init_textdomain() {
        $path = dirname( __FILE__ ) . '/languages';
        load_plugin_textdomain( self::SLUG, false, $path );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Soubory s licencí pro lékaře a kosmetology', 'odwpwc-webtrh20170412' ),
                'odwpwcw_settings_section_callback',
                self::SLUG
        );

        add_settings_field(
                'upload_dir',
                __( 'Název složky pro upload', self::SLUG ),
                [__CLASS__, 'render_setting_upload_dir'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'max_file_size',
                __( 'Maximální velikost souboru', self::SLUG ),
                [__CLASS__, 'render_setting_max_file_size'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'allowed_extensions',
                __( 'Povolené typy souborů', self::SLUG ),
                [__CLASS__, 'render_setting_allowed_extensions'],
                self::SLUG,
                $section1
        );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     */
    public static function admin_menu() {
        add_options_page(
                __( 'Nastavení pro plugin Úpravy WooCommerce', self::SLUG ),
                __( 'Úpravy WooCommerce', self::SLUG ),
                'manage_options',
                self::SLUG,
                [__CLASS__, 'admin_options_page']
            );
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     */
    public static function admin_enqueue_scripts( $hook ) {
        if( $page == 'settings_page_odwpwc-webtrh20170412' ) {
            wp_enqueue_script( self::SLUG, plugins_url( 'js/admin.js', __FILE__ ), ['jquery'] );
            wp_localize_script( self::SLUG, 'odwpwcw20170412', [
                //...
            ] );

            wp_enqueue_style( self::SLUG, plugins_url( 'css/admin.css', __FILE__ ) );
        }
    }

    /**
     * Renders plugin's options page.
     * @return void
     */
    public static function admin_options_page() { 
?>
<form action="options.php" method="post">
    <h2><?php _e( 'Nastavení pro plugin Úpravy WooCommerce', self::SLUG ) ?></h2>
<?php
        settings_fields( self::SLUG );
        do_settings_sections( self::SLUG );
        submit_button();
?>
</form>
<?php
    }

    /**
     * Renders settings section 1.
     * @return void
     */
    public static function render_settings_section_1() {
?>
<p class="description">
    <?php _e( '...', self::SLUG ) ?>
</p>
<?php
    }

    /**
     * Renders input for "upload_dir" setting.
     * @return void
     */
    public static function render_setting_upload_dir() {
        $options = self::get_options();
?>
<input type="text" name="odwpwcw_settings[upload_dir]" value="<?= $options['upload_dir'] ?>" class="normal">
<p class="description">
    <?php printf(
            __( 'Zadejte název pro služku, do které se budou nahrávat licence. Tato složka bude umístěna uvnitř hlavní složky pro nahrané soubory (tzn. <code>%s</code>).', self::SLUG ),
            str_replace( get_home_url( '/' ), '', wp_upload_dir()['baseurl'] )
    ) ?>
</p>
<?php
    }

    /**
     * Renders input for "max_file_size" setting.
     * @return void
     */
    public static function render_setting_max_file_size() {
    	$options = self::get_options();
        $max_size = self::get_max_upload_size();
?>
<p>
    <?php _e( 'Zadejte velikost v bajtech ', self::SLUG ) ?>
    <input type="number" name="odwpwcw_settings[max_file_size]" value="<?= $options['max_file_size'] ?>" min="0" max="<?= $max_size ?>">&nbsp;
    <span><?php _e( ' nebo vyberte jednu z těchto možností ', self::SLUG ) ?></span>&nbsp;
    <button class="button"><?php _e( '1 MB', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( '3 MB', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( '5 MB', self::SLUG ) ?></button>
    <span><?php _e( ' nebo ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'maximální', self::SLUG ) ?></button>
    <span><?php _e( '.', self::SLUG ) ?></span>
</p>
<p class="description">
    <?php printf(
            __( 'Zvolte maximální velikost pro soubory s nahranými licencemi. Defaultní hodnota je maximální možná velikost pro nahrávané soubory stanovená nastavením serveru a PHP (na tomto serveru to je <strong>%s</strong>).', self::SLUG ),
            size_format( $max_size, 2 )
    ) ?>
</p>
<?php
    }

    /**
     * Renders input for "allowed_extensions" setting.
     * @return void
     */
    public static function render_setting_allowed_extensions() {
    	$options = self::get_options();
?>
<p>
    <?php _e( 'Zadejte přípony ', self::SLUG ) ?>
    <input type="text" name="odwpwcw_settings[allowed_extensions]" value="<?= $options['allowed_extensions'] ?>">&nbsp;
    <span><?php _e( ' nebo vyberte některé z těchto možností ', self::SLUG ) ?></span>&nbsp;
    <button class="button"><?php _e( 'JPG', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'GIF', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'PNG', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'BMP', self::SLUG ) ?></button>
    <span><?php _e( ', ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'WebP', self::SLUG ) ?></button>
    <span><?php _e( ' nebo ', self::SLUG ) ?></span>
    <button class="button"><?php _e( 'všechny', self::SLUG ) ?></button>
    <span><?php _e( '.', self::SLUG ) ?></span>
</p>
<p class="description">
    <?php _e( 'Zadejte přípony typů podporovaných obrázků oddělené pro soubory s licencí nebo je vyberte pomocí tlačítek. Jednotlivé typy oddělujte čárkou (např. <code>jpg,gif,png</code>, v nejjednodušším případě jen <code>jpg</code> atp.).', self::SLUG ) ?>
</p>
<?php
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     */
    public static function plugins_loaded() {
        add_action( 'woocommerce_register_form_start', [__CLASS__, 'wc_register_form_start'] );
        add_action( 'woocommerce_register_form', [__CLASS__, 'wc_register_form'] );
        add_action( 'woocommerce_register_form_end', [__CLASS__, 'wc_register_form_end'] );
        add_action( 'woocommerce_register_post', [__CLASS__, 'wc_validate_register_form'], 10, 3 );
        add_action( 'woocommerce_created_customer', [__CLASS__, 'wc_created_customer'] );
    }
    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     * @todo Used page's slug should be taken from WooCommerce's option!!!
     * @todo Implement maximum allowed file size as a plugin's option.
     * @todo Implement allowed file extensions as a plugin's option.
     * @todo Print errors directly inside the form not as an alerts!
     */
    public static function enqueue_scripts() {
        if ( is_page( 'muj-ucet' ) ) {
            wp_enqueue_script( self::SLUG, plugins_url( 'js/public.js', __FILE__ ), ['jquery'] );
            wp_localize_script( self::SLUG, 'odwpwcw20170412', [
                'ROLE_CUSTOMER' => self::ROLE_CUSTOMER,
                'msg1'          => __( 'Nevložili jste soubor s licencí!', self::SLUG ),
                'msg2'          => __( 'Soubor je špatného typu nebo je příliš veliký!', self::SLUG ),
                'file_size'     => self::get_option( 'max_file_size' ),
                'allowed_ext'   => self::get_option( 'allowed_extensions' ),
            ] );

            wp_enqueue_style( self::SLUG, plugins_url( 'css/public.css', __FILE__ ) );
        }
    }

    /**
     * Hook for "woocommerce_created_customer" action.
     * @param int $customer_id
     * @return void
     */
    public static function wc_created_customer( $customer_id ) {
        $user_role  = sanitize_text_field( filter_input( INPUT_POST, 'user_role' ) );
        $first_name = sanitize_text_field( filter_input( INPUT_POST, 'first_name' ) );
        $last_name  = sanitize_text_field( filter_input( INPUT_POST, 'last_name' ) );
        $license    = sanitize_text_field( filter_input( INPUT_POST, 'license' ) );

        if ( empty( $user_role ) ) {
            $user_role = self::ROLE_CUSTOMER;
        }

        update_user_meta( $customer_id, 'user_role', $user_role );

        if ( ! empty( $first_name ) ) {
            update_user_meta( $customer_id, 'first_name', $first_name );
            // First name field which is used in WooCommerce
            update_user_meta( $customer_id, 'billing_first_name', $first_name );
        }

        if ( ! empty ( $last_name ) ) {
            update_user_meta( $customer_id, 'last_name', $last_name );
            // Last name field which is used in WooCommerce
            update_user_meta( $customer_id, 'billing_last_name', $last_name );
        }

        if ( ! empty( $license ) ) {
            update_user_meta( $customer_id, 'license', $license );
        }
    }

    /**
     * Customize WooCommerce registration form from the begining.
     * @return void
     */
    public static function wc_register_form_start() {
        ob_start( function() {} );
        include_once( dirname( __FILE__ ) . '/html/reg_form_1.phtml' );
        echo ob_get_flush();
    }

    /**
     * Customize WooCommerce registration form.
     * @return void
     */
    public static function wc_register_form() {
        ob_start( function() {} );
        include_once( dirname( __FILE__ ) . '/html/reg_form_2.phtml' );
        echo ob_get_flush();
    }

    /**
     * Customize WooCommerce registration form at the end.
     * @return void
     */
    public static function wc_register_form_end() {
        ob_start( function() {} );
        include_once( dirname( __FILE__ ) . '/html/reg_form_3.phtml' );
        echo ob_get_flush();
    }

    /**
     * Validate our customizations of WooCommerce registration form.
     * @param string $username
     * @param string $email
     * @param WP_Error $errors
     * @return WP_Error Validation errors.
     */
    public static function wc_validate_register_form( $username, $email, WP_Error $errors ) {
        $user_role  = filter_input( INPUT_POST, 'user_role' );
        $first_name = filter_input( INPUT_POST, 'first_name' );
        $last_name  = filter_input( INPUT_POST, 'last_name' );

        if ( ! in_array( $user_role, self::AVAILABLE_ROLES ) ) {
            $errors->add( 'user_role_error', __( 'Musíte si zvolit odpovídající uživatelskou roli!.', self::SLUG ) );
        }

        if ( empty( $first_name ) ) {
            $errors->add( 'first_name_error', __( 'Křestní jméno je povinné!', self::SLUG ) );
        }

        if ( empty( $last_name ) ) {
            $errors->add( 'last_name_error', __( 'Příjmení je povinné!.', self::SLUG ) );
        }

        // TODO Check if "license" is uploaded and it is JPG (max. 5MB).
        $roles = [ self::ROLE_COSMETOLOGIST, self::ROLE_PHYSICIAN ];
        if ( in_array( $user_role, $roles ) && $_FILES ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            foreach ($_FILES as $file => $file_arr) {
                $img_post_id = media_handler_upload( $file );

                if ( is_wp_error( $img_post_id ) ) {
                    $errors->add(
                        'license_error',
                        sprintf(
                            __( 'Při nahrávání souboru s licencí nastala chyba "%s"!.', self::SLUG ),
                            $img_post_id->get_error_message()
                        )
                    );
                } else {
                    // XXX self::license_img_post_id = $img_post_id;
                    // $image_post_id now holds the post ID of an attachment that is your uploaded file
                }
            }
        }
        else if ( in_array( $user_role, $roles ) && ! $_FILES ) {
            $errors->add(
                'license_error',
                __( 'Musíte nahrát soubor s licencí!.', self::SLUG )
            );
        }

        return $errors;
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     */
    private static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * @internal Check requirements of the plugin.
     * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
     * @return boolean Returns TRUE if requirements are met.
     * @todo Current solution doesn't work for WPMU...
     */
    public static function requirements_check() {
        $active_plugins = (array) get_option( 'active_plugins', [] );
        return in_array( 'woocommerce/woocommerce.php', $active_plugins ) ? true : false;
    }

    /**
     * @internal Shows error in WP administration that minimum requirements were not met.
     * @return void
     */
    public static function requirements_error() {
        self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> vyžaduje, aby byl nejprve nainstalovaný a aktivovaný plugin <b>WooCommerce</b>.', 'odwpwcgp' ), 'error' );
        self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> byl <b>deaktivován</b>.', 'odwpwcgp' ), 'updated' );
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @return void
     */
    protected static function print_error( $msg, $type = 'info' ) {
        $avail_types = ['error', 'info', 'updated'];
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }

    /**
     * @internal Returns a file size limit in bytes based on the PHP settings.
     * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     * @return int
     */
    protected static function get_max_upload_size() {
        static $max_size = -1;

        if( $max_size < 0 ) {
            // Start with post_max_size.
            $max_size = self::parse_size( ini_get( 'post_max_size' ) );

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size( ini_get( 'upload_max_filesize' ) );
            if( $upload_max > 0 && $upload_max < $max_size ) {
                $max_size = $upload_max;
            }
        }

        return $max_size;
    }

    /**
     * @internal Parses file size string from PHP settings (e.g. 1M etc.).
     * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     * @param string $size
     * @return int
     */
    protected static function parse_size( $size ) {
        // Remove the non-unit characters from the size.
        $unit = preg_replace( '/[^bkmgtpezy]/i', '', $size );
        // Remove the non-numeric characters from the size.
        $size = preg_replace( '/[^0-9\.]/', '', $size );

        if ( $unit ) {
            // Find the position of the unit in the ordered string which is 
            // the power of magnitude to multiply a kilobyte by.
            return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[0] ) ) );
        }

        return round( $size );
    }
} // End of odwpcp_webtrh20170412

endif;

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
