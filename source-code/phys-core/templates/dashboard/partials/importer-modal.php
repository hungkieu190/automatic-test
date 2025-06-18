<?php
$theme_data     = Phys_Theme_Manager::get_metadata();
$links          = $theme_data['links'];
$link_dashboard = Phys_Dashboard::get_link_main_dashboard();

?>

<div class="tc-modal-importer md-modal md-effect-16" data-template="phys-form-import">
</div>
<div class="md-overlay"></div>

<script type="text/html" id="tmpl-phys-form-import">
	<div class="md-content">
		<h3 class="title"><?php esc_html_e( 'Import Demo', 'phys-core' ); ?> <span class="demo-name"></span><span
				class="close"></span></h3>
		<div class="main">
			<form id="form-importer">
				<div class="pre-import">
					<h4><?php esc_html_e( 'Pre-import', 'phys-core' ); ?></h4>
					<ul class="options">
						<li class="package plugins obligatory" data-package="plugins">
							<label>
								<input type="checkbox" id="importer-plugins" checked="checked" disabled="disabled">
							</label>
							<div class="heading"><?php esc_html_e( 'Required Plugins', 'phys-core' ); ?></div>
							<div
								class="description"><?php esc_html_e( 'This will install and active plugins required and it is obligatory.', 'phys-core' ); ?></div>
							<span class="package-progress-bar"></span>
						</li>
						<li class="package hidden" data-package="download_demo_data">
							<label>
								<input type="checkbox" id="download-demo-data" checked="checked" disabled="disabled">
							</label>
						</li>
					</ul>
				</div>

				<div class="import-content">
					<h4><?php esc_html_e( 'Select what type of content you want to import', 'phys-core' ); ?></h4>

					<?php
					$packages = Phys_Importer::get_import_packages();
					if ( count( $packages ) ) :
						?>
						<ul class="options">
							<?php foreach ( $packages as $key => $package ) : ?>
								<li class="package <?php echo esc_attr( $key ); ?>"
									data-required="<?php echo esc_attr( isset( $package['required'] ) ? $package['required'] : '' ); ?>"
									data-package="<?php echo esc_attr( $key ); ?>">
									<label>
										<input type="checkbox" id="importer-<?php echo esc_attr( $key ); ?>"
											   checked="checked">
									</label>
									<div class="heading"><?php echo esc_html( $package['title'] ); ?></div>
									<div class="description"><?php echo esc_html( $package['description'] ); ?></div>
									<span class="package-progress-bar"></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</form>
		</div>

		<div class="footer">
			<button class="button button-primary tc-button" id="start-import"
					data-text="<?php esc_attr_e( 'Import', 'phys-core' ); ?>"
					data-importing="<?php esc_attr_e( 'Importing', 'phys-core' ); ?>"
					data-completed="<?php esc_attr_e( 'Completed', 'phys-core' ); ?>"></button>

			<div
				class="text-waiting"><?php esc_html_e( 'Enjoy a cup of coffee while you are waiting for importing :)', 'phys-core' ); ?></div>
		</div>

		<div class="wrapper-finish">
			<div class="full-box">
				<div class="middle notification text-center">
					<a class="icon" href="<?php echo esc_url( Phys_Product_Registration::get_link_reviews() ); ?>"
					   target="_blank"></a>
					<div class="details-error">
						<h3></h3>
						<div class="try-again">
							<button class="button button-primary tc-button"
									id="retry-import"><?php esc_html_e( 'Try again', 'phys-core' ); ?></button>
						</div>

						<div class="get-support">
							<a target="_blank" href="<?php echo esc_url( $links['docs'] ); ?>"
							   class="button button-secondary tc-button"><?php esc_html_e( 'Documentation', 'phys-core' ); ?></a>
						</div>
					</div>
					<div class="details-success">
						<h3><?php esc_html_e( 'Hooray! All Done.', 'phys-core' ); ?></h3>
						<p><?php printf( __( 'View <a href="%1$s" target="_blank">your site</a> or return to <a href="%2$s">dashboard</a>.', 'phys-core' ), home_url( '/' ), $link_dashboard ); ?></p>
						<a class="leave-five-stars"
						   href="<?php echo esc_url( Phys_Product_Registration::get_link_reviews() ); ?>"
						   target="_blank">
							<?php
							wp_star_rating(
								array(
									'rating' => 5,
								)
							);
							?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
