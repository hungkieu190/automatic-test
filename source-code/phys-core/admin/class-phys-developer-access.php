<?php

/**
 * Class Phys_Developer_Access
 *
 * @since 1.2.1
 */
class Phys_Developer_Access extends Phys_Singleton {
    /**
     * Is granted.
     *
     * @since 1.2.1
     *
     * @return true
     */
    public static function is_granted() {
        $instance = self::instance();

        $valid = $instance->validate();
        if ( ! $valid ) {
            return false;
        }

        return true;
    }

    /**
     * Get link developer access.
     *
     * @since 1.2.1
     *
     * @return bool|string
     */
    public static function get_link_access() {
        $instance = self::instance();
        $data     = $instance->get_token();

        if ( ! $data ) {
            return false;
        }

        $token = $data['token'];
        $owner = $data['owner'];
        if ( empty( $token ) || ! is_numeric( $owner ) ) {
            return false;
        }

        $base = site_url( 'wp-login.php?action=tc-developer-access' );

        return add_query_arg( array(
            'access_token' => $token,
            'access_id'    => $owner
        ), $base );
    }

    /**
     * Phys_Developer_Access constructor.
     *
     * @since 1.2.1
     */
    protected function __construct() {
        $this->hooks();
    }

    /**
     * Add hooks.
     *
     * @since 1.2.0
     */
    private function hooks() {
        add_action( 'login_form_tc-developer-access', array( $this, 'request_access' ) );
        add_action( 'phys_core_grant_developer_access', array( $this, 'grant_developer_access' ) );
        add_action( 'phys_core_developer_access_box', array( $this, 'box_manage_developer_access' ) );
        add_action( 'admin_init', array( $this, 'handle_request' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
    }

    /**
     * Enqueue scripts.
     *
     * @since 1.2.1
     */
    public function enqueue_scripts() {
        wp_register_script( 'phys-developer-access', PHYS_CORE_ADMIN_URI . '/assets/js/developer-access.js', array(
            'jquery',
            'phys-core-clipboard'
        ), PHYS_CORE_VERSION );
    }

    /**
     * Handle request grant/revoke developer access.
     *
     * @since 1.2.1
     */
    public function handle_request() {
        $detect = isset( $_POST['phys_core_developer_access'] );

        if ( ! $detect ) {
            return;
        }

        if ( ! check_admin_referer( 'phys_core_developer_access', 'phys_core_developer_access' ) ) {
            return;
        }

        $revoke = isset( $_POST['tc-revoke-developer-access'] );
        if ( $revoke ) {
            $this->destroy_token();

            return;
        }

        $grant = isset( $_POST['tc-grant-developer-access'] );
        if ( $grant ) {
            $this->create_access();
        }
    }

    /**
     * Validate token
     *
     * @since 1.2.0
     *
     * @return bool
     */
    private function validate() {
        $data = $this->get_token();
        if ( ! $data ) {
            return false;
        }

        $token      = $data['token'];
        $owner      = $data['owner'];
        $expires_in = $data['expires_in'];
        $created_at = $data['created_at'];

        if ( ! is_numeric( $owner ) ) {
            return false;
        }

        if ( strlen( $token ) !== 32 ) {
            return false;
        }

        if ( ! is_numeric( $created_at ) || ! is_numeric( $expires_in ) ) {
            return false;
        }

        $now  = time();
        $time = $now - $created_at;
        if ( $time > $expires_in ) {
            return false;
        }

        return true;
    }

    /**
     * Box manage developer access.
     *
     * @since 1.2.1
     */
    public function box_manage_developer_access() {
        $enable = apply_filters( 'phys_core_enable_developer_access', true );
        if ( ! $enable ) {
            return;
        }

        Phys_Template_Helper::template( 'developer-access.php', array(), true );
    }

    /**
     * Grant developer access.
     *
     * @since 1.2.1
     */
    public function grant_developer_access() {
        $this->create_access();
    }

    /**
     * Handle request developer access.
     *
     * @since 1.2.1
     */
    public function request_access() {
        $token   = isset( $_GET['access_token'] ) ? $_GET['access_token'] : '';
        $user_id = isset( $_GET['access_id'] ) ? $_GET['access_id'] : '';
        $token   = sanitize_text_field( $token );
        $user_id = sanitize_text_field( $user_id );

        if ( empty( $token ) || empty( $user_id ) ) {
            return;
        }

        $user_id = intval( $user_id );
        if ( ! $this->check_access( $token, $user_id ) ) {
            return;
        }

        wp_set_auth_cookie( $user_id );
        phys_core_redirect( admin_url() );
    }

    /**
     * Get time expiration.
     *
     * @since 1.2.1
     *
     * @return int
     */
    private function get_expiration() {
        $day = 60;

        return $day * 24 * 3600;
    }

    /**
     * Grant access.
     *
     * @since 1.2.1
     *
     * @return bool
     */
    private function create_access() {
        $user    = wp_get_current_user();
        $user_id = $user->ID;

        if ( ! $user_id ) {
            return false;
        }
        $created_at = time();
        $expiration = $this->get_expiration();
        $token      = phys_core_generate_token();

        $data = array(
            'token'      => $token,
            'owner'      => $user_id,
            'expires_in' => $expiration,
            'created_at' => $created_at
        );

        return update_option( 'phys_core_developer_access', $data );
    }

    /**
     * Check access.
     *
     * @since 1.2.1
     *
     * @param $access_token
     * @param $user_id
     *
     * @return bool
     */
    private function check_access( $access_token, $user_id ) {
        if ( ! $this->validate() ) {
            return false;
        }

        $data  = $this->get_token();
        $token = $data['token'];
        $owner = intval( $data['owner'] );

        if ( $owner !== $user_id ) {
            return false;
        }

        if ( $token !== $access_token ) {
            return false;
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return false;
        }

        return true;
    }

    /**
     * Destroy token.
     *
     * @since 1.2.1
     */
    private function destroy_token() {
        update_option( 'phys_core_developer_access', false );
    }

    /**
     * Get token.
     *
     * @since 1.2.1
     *
     * @return array|bool
     */
    private function get_token() {
        $option = get_option( 'phys_core_developer_access', false );

        if ( ! is_array( $option ) ) {
            return false;
        }

        return wp_parse_args( $option, array(
            'token'      => '',
            'owner'      => false,
            'expires_in' => false,
            'created_at' => false,
        ) );
    }
}
