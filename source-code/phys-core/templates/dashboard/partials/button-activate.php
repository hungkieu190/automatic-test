<?php

if ( Phys_Envato_Hosted::is_envato_hosted_site() ) {
    return Phys_Dashboard::get_template( 'partials/button-verify-envato-site.php' );
}
 $url_args = array(
	'page' => 'phys-license',
);

if ( ! empty( $args['return'] ) ) {
	$url_args['phys_redirect'] = urlencode( $args['return'] );
}

$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
?>
<a class="button button-primary tc-button" href="<?php echo esc_url( $url ); ?>">
	<?php esc_html_e( 'Activate theme', 'phys-core' ); ?>
</a>
