<?php

do_action( 'phys_core_background_check_update_theme' );

$theme_data      = Phys_Theme_Manager::get_metadata();
$template        = $theme_data['template'];
$current_version = $theme_data['version'];

$update_themes = Phys_Product_Registration::get_update_themes();
$themes        = $update_themes['themes'];
$last_checked  = $update_themes['last_checked'];

$data = isset( $themes[$template] ) ? $themes[$template] : false;

$is_active  = Phys_Product_Registration::is_active();
$link_check = Phys_Dashboard::get_link_main_dashboard(
	array(
		'force-check' => 1,
	)
);
$can_update = Phys_Theme_Manager::can_update();

$class          = $is_active ? 'active-registration' : 'inactive-registration';
$personal_token = Phys_Product_Registration::get_data_theme_register( 'personal_token' );
$theme_name     = isset( $data ) && $data ? $data['name'] : '';
?>
<div class="tc-box-body">
	<div class="td-box-welcome-admin">
		<div class="box-left">
			<p>
				<?php printf(
					__(
						'Thanks for installing the %1$s theme. If this is the first time you work with %1$s, please read and follow
			the instructions carefully. This is the getting started section of %1$s Theme Dashboard.', 'phys-core'
					), $theme_name
				); ?>
			</p>
			<a href="<?php echo admin_url( 'admin.php?page=phys-getting-started' ); ?>"
			   class="tc-button-box tc-button-black"><?php esc_html_e( 'Let\'s get started', 'phys-core' ); ?></a>
		</div>
		<div class="box-right">
			<?php
			if ( ! $data ) {
				$data_info = array(
					'title'      => __( 'Something went wrong!', 'phys-core' ),
					'desc'       => __( 'Please try again later.', 'phys-core' ),
					'class'      => 'no-info-theme',
					'btn-update' => '',
				);
			} else {
				if ( $can_update ) {
					$data_info = array(
						'title' => sprintf( __( '<span style="color: red">Version %1$s</span> available.', 'phys-core' ), $data['version'] ),
  						'class' => 'has-update',
						'desc'  => __( 'Your Version is', 'phys-core' ) . ' ' . $current_version
					);
					if ( $is_active ) {
						if ( empty( $personal_token ) ) {
							$data_info['btn-update'] = '<div class="update-notice"><a href="' . esc_url( admin_url( '/admin.php?page=phys-license' ) ) . '" >' . esc_html__( 'Add personal token', 'phys-core' ) . '</a></div>';
						} else {
							$data_info['btn-update'] = '<div class="update-message"><button class="button-link tc-update-now" type="button">' . esc_html__( 'Update now', 'phys-core' ) . '</button></div>';
						}
					} else {
						$data_info['btn-update'] = '<div class="update-notice"><a href="' . esc_url( admin_url( '/admin.php?page=phys-license' ) ) . '" class="active-theme-now">' . esc_html__( 'Active Theme Now', 'phys-core' ) . '</a></div>';
					}
				} else {
					$data_info = array(
						'title'      => __( 'Theme is up to date', 'phys-core' ),
						'class'      => 'no-update',
						'desc'       => __( 'Your Version is', 'phys-core' ) . ' ' . $current_version,
						'btn-update' => '',
					);
				}
			}

			$requirements_notification = apply_filters( 'phys_core_number_requirements_notification', 0 );
			if ( $requirements_notification > 0 ) {
				$requiremen_info = array(
					'title' => __( 'Something wrong', 'phys-core' ),
					'desc'  => __( 'We detected ' . $requirements_notification . ' issues', 'phys-core' ),
					'link'  => admin_url( 'admin.php?page=phys-system-status' ),
					'icon'  => '',
				);
			} else {
				$requiremen_info = array(
					'title' => __( 'All is Good', 'phys-core' ),
					'desc'  => __( 'Nothing wrong found', 'phys-core' ),
					'icon'  => ' reqirements-success',
				);
			}
			?>
			<div class="box box-info-update <?php echo esc_attr( $data_info['class'] ); ?>">
				<span class="icon"></span>
				<h5><?php echo $data_info['title']; ?></h5>
				<p><?php echo $data_info['desc']; ?></p>
				<?php echo $data_info['btn-update']; ?>
			</div>
			<div class="box box-info-reqirements<?php echo $requiremen_info['icon']; ?>">
				<span class="icon"></span>
				<h5><?php echo $requiremen_info['title']; ?></h5>
				<p><?php echo $requiremen_info['desc']; ?></p>
				<?php
				if ( isset( $requiremen_info['link'] ) ) {
					echo '<div class="update-notice"><a href="' . $requiremen_info['link'] . '">Resolve now</a></div>';
				}
				?>
			</div>
		</div>
	</div>
</div>
