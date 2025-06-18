<?php
$theme_metadata  = Phys_Theme_Manager::get_metadata();
$links           = $theme_metadata['links'];
$name_keys = apply_filters('phys_name_theme_panel_active_customize', $theme_metadata['name']);

?>

<div class="top">
    <div class="row">
        <div class="col-md-12">
            <h2>Settings Your Site</h2>
            <div class="caption no-line">
                <p>Congratulations! You are all set!</p>

                <h4>What now?</h4>
                <ul class="tc-list">
                    <li>You should edit the content of the web to fit your business purpose (update images, articles...).</li>
                    <li>You should customize your website to make it fit your idea, using the advanced customize system of the theme.</li>
                    <li>Watch this video tutorial to learn how to use the Customize system and how to edit the theme easily.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="bottom">
    <a class="tc-skip-step">Skip</a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $name_keys ) ); ?>"
       class="button button-primary tc-button tc-run-step">
		<?php esc_html_e( 'Settings your site', 'phys-core' ); ?>
    </a>
</div>

