<?php $mobile_popup = '';
    if( $args['options']['enable_mobile_popup'] !== 'on') {
        $mobile_popup = 'mobile-hide-modal';
    } 
?>

<div class="md-overlay phys-hide"></div>

<?php if ( isset($_COOKIE['physcookie-consent']) ) : ?>
    <button class="physcookie-btn-revisit <?php echo esc_attr($mobile_popup);?>"
        title="<?php echo esc_attr('Consent Preferences','phys-core');?>" 
        onclick="physCustomise()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="27" fill="none" stroke="#50575E" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M12 2a10 10 0 0 0-1 19.95 10 10 0 0 0 9-14.82A2.5 2.5 0 0 1 15.5 5a2.5 2.5 0 0 1-2.45-3A10 10 0 0 0 12 2Z"/>
            <circle cx="10.5" cy="9" r="1.5"/>
            <circle cx="15.5" cy="13" r="1.5"/>
            <circle cx="7.5" cy="16" r="1.5"/>
        </svg>
    </button>
<?php endif; ?>

<div class="tc-modal <?php echo esc_attr( $args['options']['popup_position'] );?> <?php echo esc_attr($mobile_popup);?>" 
    id="<?php echo esc_attr( $args['id'] ); ?>" 
    data-template="<?php echo esc_attr( $args['id'] ); ?>"
>
    <?php echo Phys_Template_Helper::template( $args['template'], $args['options'] ); ?>
</div>
