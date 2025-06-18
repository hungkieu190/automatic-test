<?php
$token          = Phys_Product_Registration::get_data_theme_register( 'purchase_token' );
$purchase_code  = Phys_Product_Registration::get_data_theme_register( 'purchase_code' );
$personal_token = Phys_Product_Registration::get_data_theme_register( 'personal_token' );
//$my_theme_id    = Phys_Free_Theme::get_theme_id();
$user           = wp_get_current_user();

$theme_data = Phys_Theme_Manager::get_metadata();
$theme      = $theme_data['text_domain'];
$version    = $theme_data['version'];
$find_lince = 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-';

$html_personal_tocken = '<tr><th scope="row">' . esc_html__( 'Personal token (optional)', 'phys-core' ) . '</th>
							<td>
								<input type="text" id="personal_token" name="personal_token" value=""
									   placeholder="Enter personal token" autocomplete="off">
								<p>Only use for update theme.</p>
								<ol class="help-info-api">
									<li>' . sprintf( esc_html__( 'Generate an Envato API Personal Token by %s.', 'phys-core' ), '<a href="https://build.envato.com/create-token/?default=t&purchase:download=t&purchase:list=t" target="_blank">' . esc_html__( 'clicking this link', 'phys-core' ) . '</a>' ) . '</li>
									<li>' . esc_html__( 'Name the token eg “My WordPress site”.', 'phys-core' ) . '</li>
									<li>' . esc_html__( 'Ensure the following permissions are enabled:', 'phys-core' ) . '
										<ul>
											<li>' . esc_html__( 'View and search Envato sites', 'phys-core' ) . '</li>
											<li>' . esc_html__( 'Download your purchased items', 'phys-core' ) . '</li>
											<li>' . esc_html__( "List purchases you've made", 'phys-core' ) . '</li>
										</ul>
									</li>
								</ol>
							</td>
						</tr>';
$is_active            = Phys_Product_Registration::is_active();
 ?>
<div class="tc-box tc-box-theme-license">
	<div class="tc-box-header">
		<h2 class="box-title">Activate your theme license</h2>
	</div>

	<div class="tc-box-body">
		<?php
		if ( $is_active ) :
 				?>
				<div class="wrapper-message"><div class="message-success message">Your theme license is activated. Thank you!</div></div>
				<table class="form-table">
					<tbody>
					<?php if ( ! empty( $purchase_code ) ) : ?>
						<tr class="license-active">
							<th scope="row">
								<?php esc_html_e( 'Purchase code: ', 'phys-core' ); ?>
							</th>
							<td>
								<?php // Show purchase code with **** and last 3 characters of the purchase code with format uuid4 ?>
								<input type="text"
									   value="<?php echo esc_html( str_repeat( '*', strlen( $purchase_code ) - 7 ) . substr( $purchase_code, - 4 ) ); ?>"
									   disabled>
								<button
									class="phys-deactive button button-secondary tc-button deactivate-btn tc-run-step">
									<?php esc_html_e( 'Deactivate', 'phys-core' ); ?>
								</button>
							</td>
						</tr>
					<?php endif; ?>
					<?php if ( ! empty( $personal_token ) ) : ?>
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Personal token: ', 'phys-core' ); ?>
							</th>
							<td>
								<?php // Show personal token with **** and show last 3 characters of the personal token ?>
								<input type="text"
									   value="<?php echo esc_html( str_repeat( '*', strlen( $personal_token ) - 7 ) . substr( $personal_token, - 4 ) ); ?>"
									   disabled>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
				<?php if ( empty( $personal_token )  ) : ?>
					<form class="phys-form-personal" action="" method="post">
						<table class="form-table">
							<tbody>
							<?php echo $html_personal_tocken; ?>
							</tbody>
						</table>
						<button class="arrow-personal-token button button-primary tc-button" type="submit">
							<?php esc_html_e( 'Update', 'phys-core' ); ?>
						</button>
					</form>
				<?php endif; ?>
 		<?php else: ?>
			<div class="wrapper-message">
				<div class="message-info message">Activate your purchase code for this domain to turn on install plugin required and import data demo</div>
			</div>

			<form class="phys-form-license" action="" method="post">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">Purchase code <span class="required">*</span></th>
						<td>
							<input type="text" id="purchase_code" name="purchase_code" value=""
								   placeholder="Enter purchase code" autocomplete="off" required>
							<p class="find-license"><small>
									<a href="<?php echo esc_url( $find_lince ); ?>"
									   target="_blank" rel="noopener">Where can I get my purchase code?</a></small></p>
						</td>
					</tr>

					<?php if ( empty( $personal_token ) ) : ?>
						<?php echo $html_personal_tocken; ?>
					<?php endif; ?>
					</tbody>
				</table>
				<p>
					<label for="agree_stored" class="agree-label">
						<input type="checkbox" name="agree_stored" id="agree_stored" required>
						I agree that my purchase code and user data will be stored by physcode.com
					</label>
				</p>

				<input type="hidden" name="domain" value="<?php echo esc_url( site_url() ); ?>">
				<input type="hidden" name="theme" value="<?php echo esc_attr( $theme ); ?>">
				<input type="hidden" name="theme_version" value="<?php echo esc_attr( $version ); ?>">
				<input type="hidden" name="user_email"
					   value="<?php echo esc_attr( $user ? $user->user_email : '' ); ?>">

				<button class="button button-primary tc-button activate-btn tc-run-step" type="submit">
					<?php esc_html_e( 'Submit', 'phys-core' ); ?>
				</button>
			</form>
		<?php endif; ?>

	</div>
	<div class="tc-box-footer">
		<p>Note: you are allowed to use our theme only on one domain if you purchased a regular license.</p>
	</div>
</div>
