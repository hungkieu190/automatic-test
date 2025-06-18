<?php
$body_template = 'boxes/' . $args['template'] . '.php';
$locked        = $args['lock'] && ! Phys_Product_Registration::is_active();
$box_id        = isset( $args['id'] ) ? $args['id'] : '';
$class_extend  = apply_filters( 'phys_core_dashboard_box_classes', '', $box_id );
$class_extend  .= isset( $args['class'] ) ? $args['class'] : '';
$themes_active = Phys_Theme_Manager::get_metadata();
$name_keys = apply_filters('phys_name_theme_panel_active_customize', $themes_active['name']);
?>

<div class="tc-box<?php echo esc_attr( $locked ? ' locked' : '' ); ?> <?php echo esc_attr( $class_extend ); ?>"
	 data-id="<?php echo esc_attr( $args['id'] ); ?>">
	<div class="tc-box-header">
		<?php
		if ( $args['lock'] ) {
			Phys_Dashboard::get_template( 'partials/box-status.php' );
		}
		?>
		<h2 class="box-title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php if ( $args['id'] == 'appearance' ) {
			$link = apply_filters('phys_link_customize', admin_url( 'admin.php?page=' . $name_keys ));
			echo '<a href="' . $link . '" class="sub_link" target="_blank">' . __( 'Go to Theme Settings', 'phys-core' ) . '</a>';
		}
		if ( $args['id'] == 'changelog' && $args['links'] ) {
			echo '<a href="' . esc_url( $args['links'] ) . '" class="sub_link" target="_blank">' . __( 'View all Changelog', 'phys-core' ) . '</a>';
		}
		?>
	</div>

	<?php Phys_Dashboard::get_template( $body_template ); ?>
</div>
