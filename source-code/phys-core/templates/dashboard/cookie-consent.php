<div class="tc-cookie-consent-wrapper wrap">
	<?php // Registration box
        do_action( 'phys_dashboard_registration_box' );
	?>
    <div class="tc-notice tc-info">
        <div class="content">
        <?php 
            echo sprintf(
                esc_html__( 'Please read the %s to understand how you can manage Cookie Consent.', 'phys-core' ),
                '<a href="#tc-cookie-control-info">' . esc_html__( 'How to control third-party tracking scripts with cookies', 'phys-core' ) . '</a>'
            ); 
        ?>
        </div>
    </div>

    <div class="row">
        <!-- Cookie Banner Settings -->
        <div class="col-md-6 col-xs-12">
            <div class="tc-box">
                <div class="tc-box-header">
                    <h2 class="box-title">
                        <?php esc_html_e( 'Cookie Banner', 'phys-core' ); ?>
                    </h2>
                </div>
                <div class="tc-box-body">
                    <form class="cookie-consent-form" id="cookie-banner-form" action="" method="post">
                        <p>
                            <label class="" for="enable_popup">
                                <input type="checkbox" name="enable_popup" id="enable_popup" <?php if( $args['enable_popup'] == 'on' ) echo 'checked';?>>
                                <?php esc_html_e( 'Enable Cookie Consent Popup', 'phys-core' ); ?>
                            </label>
                        </p>
                        <p>
                            <label class="block-label" for="popup_position">
                                <?php esc_html_e( 'Popup Position', 'phys-core' ); ?>
                            </label>
                            <select name="popup_position" id="popup_position">
                                <?php
                                $positions = array(
                                    'top-left'     => esc_html__( 'Top Left', 'phys-core' ),
                                    'top-right'    => esc_html__( 'Top Right', 'phys-core' ),
                                    'bottom-left'  => esc_html__( 'Bottom Left', 'phys-core' ),
                                    'bottom-right' => esc_html__( 'Bottom Right', 'phys-core' ),
                                    'md-center'    => esc_html__( 'Center', 'phys-core' ),
                                );

                                foreach ( $positions as $value => $label ) {
                                    $selected = ( $args['popup_position'] === $value ) ? 'selected' : '';
                                    echo "<option value='" . esc_attr( $value ) . "' $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </p>
                        <p>
                            <label class="" for="enable_mobile_popup">
                                <input type="checkbox" name="enable_mobile_popup" id="enable_mobile_popup" <?php if( $args['enable_mobile_popup'] == 'on' ) echo 'checked';?>>
                                <?php esc_html_e( 'Show Popup on Mobile', 'phys-core' ); ?>
                            </label>
                        </p>
                        <p>
                            <label class="block-label" for="consent_message">
                                <?php esc_html_e( 'Consent Message', 'phys-core' ); ?>
                            </label>
                            <?php
                                wp_editor($args['consent_message'], 'consent_message', [
                                    'textarea_name' => 'consent_message',
                                    'media_buttons' => false,
                                    'textarea_rows' => 4,
                                    'tinymce' => true,
                                    'quicktags' => true
                                ]);
                            ?>
                        </p>
                        <p>
                            <label class="block-label" for="customise_consent_mess">
                                <?php esc_html_e( 'Customise Consent Message', 'phys-core' ); ?>
                            </label>
                            <?php
                                wp_editor($args['customise_consent_mess'], 'customise_consent_mess', [
                                    'textarea_name' => 'customise_consent_mess',
                                    'media_buttons' => false,
                                    'textarea_rows' => 5,
                                    'tinymce' => true,
                                    'quicktags' => true
                                ]);
                            ?>
                        </p>

                        <?php wp_nonce_field( 'cookie_consent_settings_nonce', 'cookie_consent_nonce' ); ?>
                        <button class="button button-primary tc-button" type="submit">
                            <?php esc_html_e( 'Save Changes', 'phys-core' ); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Cookie Manager: Categories -->
        <div class="col-md-6 col-xs-12">
            <div class="tc-box">
                <div class="tc-box-header">
                    <h2 class="box-title">
                        <?php esc_html_e( 'Cookie Manager', 'phys-core' ); ?>
                    </h2>
                </div>

                <?php
                    $cookie_category = isset($_GET['cookie-category']) ? sanitize_text_field($_GET['cookie-category']) : 'necessary';
                    $categories      = isset($args['cookie_categories']) ? $args['cookie_categories'] : [];
                    $cookie_list     = isset($args['cookie_list']) ? $args['cookie_list'] : [];
                ?>
                <div class="tc-box-body">
                    <p> 
                        <label style="display: inline-block; margin-right: 20px;" for="cookie_category">
                            <?php esc_html_e( 'Select Categories', 'phys-core' ); ?>
                        </label>
                        <select name="cookie_category" id="cookie_category">
                            <?php foreach ( $categories as $category_key => $category_data ) {
                                $selected = ( $cookie_category === $category_key ) ? 'selected' : '';
                                echo "<option value='" . esc_attr( $category_key ) . "' $selected>" . esc_html( $category_data['title'] ) . "</option>";
                            } ?>
                        </select>
                    </p>

                    <form class="cookie-consent-form" id="cookie-manager-form" action="" method="post">
                        <?php
                            $data = array(
                                'cookie_category' => $cookie_category, 
                                'categories'      => $categories,
                                'cookie_list'     => $cookie_list
                            );
                            Phys_Template_Helper::template( 'cookie-category-fields.php', $data, true );
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scan for get List Cookie Used On Your Site -->
    <div class="row" id="physcookie-scanner">
        <div class="col-md-12 col-xs-12">
            <div class="tc-box">
                <div class="tc-box-header">
                    <h2 class="box-title">
                        <?php esc_html_e( 'All Cookie List', 'phys-core' ); ?>
                    </h2>
                </div>
                <div class="tc-box-body">
                    <div class="table-wrapper">
                        <table id="cookie-scan-list-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Cookie Name (ID)', 'phys-core' ); ?></th>
                                    <th><?php esc_html_e( 'Domain', 'phys-core' ); ?></th>
                                    <th><?php esc_html_e( 'Description', 'phys-core' ); ?></th>
                                    <th><?php esc_html_e( 'Duration', 'phys-core' ); ?></th>
                                    <th><?php esc_html_e( 'Type', 'phys-core' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cookies will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Notice: How to control third-party tracking scripts with physcookies -->
    <div class="tc-notice tc-info" id="tc-cookie-control-info">
        <div class="content">
            <h2 style="font-size: 1.4em;"><?php echo esc_html__('📝 How to control third-party tracking scripts with cookies', 'phys-core'); ?></h2>
            <p>
                <?php echo esc_html__('To ensure Cookie Consent works properly and respects user privacy, you must modify third-party tracking codes (e.g. GA, FB, ..) to use a special format as below.', 'phys-core'); ?>
            </p>

            <h3><?php echo esc_html__('❌ Incorrect usage:', 'phys-core'); ?></h3>
            <pre style="background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; color: #721c24;">
            &lt;script src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"&gt;&lt;/script&gt;
            </pre>

            <h3><?php echo esc_html__('✅ Correct usage (cookie controlled):', 'phys-core'); ?></h3>
            <pre style="background: #e6f7ff; padding: 10px; border-left: 4px solid #007bff; color: #004085;">
            &lt;<span style="background: #fff3cd; color: #856404; font-weight: bold;">script type="text/plain" data-physcookie-category="analytics"</span> 
            src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"&gt;&lt;/script&gt;
            </pre>

            <p>
                <?php echo esc_html__('The plugin will automatically detect script tags with:', 'phys-core'); ?>
                <ul>
                    <li><code>type="text/plain"</code></li>
                    <li><code>data-physcookie-category="..."</code></li>
                </ul>
                <?php echo esc_html__('and only activate them if the user has consented to the corresponding cookie group.', 'phys-core'); ?>
            </p>

            <h3><?php echo esc_html__('🔖 Supported cookie groups:', 'phys-core'); ?></h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="background: #f1f1f1;">
                    <th style="text-align: left; padding: 8px; border: 1px solid #ccc;"><?php echo esc_html__('Group Name', 'phys-core'); ?></th>
                    <th style="text-align: left; padding: 8px; border: 1px solid #ccc;"><?php echo esc_html__('data-physcookie-category value', 'phys-core'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;"><?php echo esc_html__('Analytics', 'phys-core'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc;"><code>analytics</code></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;"><?php echo esc_html__('Advertisement', 'phys-core'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc;"><code>ads</code></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;"><?php echo esc_html__('Functional', 'phys-core'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ccc;"><code>functional</code></td>
                </tr>
                </tbody>
            </table>

            <h3><?php echo esc_html__('💡 Full example with multiple groups:', 'phys-core'); ?></h3>
            <pre style="background: #f4f4f4; padding: 10px; border-left: 4px solid #28a745; color: #2f6627;">
                &lt;script type="text/plain" data-physcookie-category="analytics" 
                src="https://www.googletagmanager.com/gtag/js?id=GA_ID"&gt;&lt;/script&gt;

                &lt;script type="text/plain" data-physcookie-category="ads"&gt;
                // Ad code here
                &lt;/script&gt;

                &lt;script type="text/plain" data-physcookie-category="functional"&gt;
                // Chat widgets or other functionality scripts
                &lt;/script&gt;
            </pre>
        </div>
    </div>
</div>
