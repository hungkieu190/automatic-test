<div class="top">
    <h2>Theme Updates</h2>

    <div class="caption no-line">
        <p>To improve the quality of the theme and to fix bugs, we frequently update our theme. Therefore, we higly
            recommend you to enable automatic theme update function.<br>
            In order for the theme to be automatically updated in the future (and save your time), please Login with
            Envato and enable Theme Updates.</p>

        <p>When there is a new update, your Dashboard will display a message with the option to install the new update.
            By clicking Login with Envato, you will be redirected to ThemeForest login page to login to your ThemeForest
            account and give permission to enable Automatic Updates.</p>
        <p>Please <a href="https://physcode.com/contact-us/" target="_blank">contact us</a> if you have any questions
            or problems.</p>
     </div>
</div>

<div class="bottom">
	<a class="tc-skip-step">Skip</a>
    <?php
    $return = Phys_Getting_Started::get_link_redirect_step( 'install-plugins' );
    Phys_Dashboard::get_template(
        'partials/button-activate.php',
        array(
            'return' => $return,
        )
    );
    ?>
</div>
