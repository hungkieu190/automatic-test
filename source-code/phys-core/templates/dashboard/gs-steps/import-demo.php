<?php
$theme_metadata = Phys_Theme_Manager::get_metadata();
$theme_name     = $theme_metadata['name'];
?>

<div class="top">
    <h2>Import Demo Content</h2>

    <div class="caption">
        <p>You are almost done!</p>
        <p>Please choose a demo content that you like the most. These demos are all amazing, so it may take you a while to choose and a little more time to install.</p>
    </div>

	<?php
	do_action( 'phys_dashboard_main_page_importer' );
	do_action( 'phys_importer_modals' );
	?>
</div>

<div class="bottom">
    <a class="tc-skip-step">Skip</a>
    <button class="button button-primary tc-button tc-run-step"><?php esc_html_e( 'Next step →', 'phys-core' ); ?></button>
</div>
