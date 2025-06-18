<div class="tc-box-body">
	<div class="text-panel-customizer">
		<?php
		/**
		 * Documentation links
		*/
		if ( apply_filters( 'phys-support-customizer', true ) ) { 
			$customizer_section_default = array(
				'panel'   => array( 'general', 'widgets', 'nav_menus' ),
				'section' => array( 'typography' )
			);
			$customizer_section = apply_filters( 'phys_theme_options_section', $customizer_section_default );
			
			foreach ( $customizer_section as $key => $values ) {
				if ( $values ) {
					foreach ( $values as $value ) {
						echo '<a class="tc-button-box tc-base-color" target="_blank" href="' . wp_customize_url() . '?autofocus[' . $key . ']=' . $value . '">' . esc_html( str_replace( "_", " ", $value ) ) . '</a>';
					}
				}
			}
		} else {
			$themes_active = Phys_Theme_Manager::get_metadata();
			$name_keys 	   = apply_filters('phys_name_theme_panel_active_customize', $themes_active['name']);
			$link          = admin_url( 'admin.php?page=' . $name_keys);

			$customizer_section_default = array(
				'general', 'header', 'display_setting', 'woo_setting'
			);
			$customizer_section = apply_filters( 'phys_theme_options_section', $customizer_section_default );

			foreach ( $customizer_section as $key => $values ) {
				if ( $values ) {
					echo '<a class="tc-button-box tc-base-color" target="_blank" href="' . $link . '&tab=' . $key . '">' . esc_html( str_replace( "_", " ", $values ) ) . '</a>';
				}
			}
		}
		
		?>
	</div>
</div>
