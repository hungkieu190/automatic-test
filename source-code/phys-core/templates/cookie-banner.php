<!-- First visit website or haven't accepted/rejected cookie consent -->
<?php if ( !isset($_COOKIE['physcookie-consent']) ) : ?>
    <div class="physcookie-banner" id="physcookie-banner">
        <div class="message">
            <?php echo $args['consent_message']; ?>
        </div>

        <div class="cookie-action">
            <button class="btn-outline" onclick="physCustomise()">
                <?php echo esc_html__('Customise', 'phys-core'); ?>
            </button>

            <button class="btn-outline" onclick="physCookieRejectAll()">
                <?php echo esc_html__('Reject All', 'phys-core'); ?>
            </button>

            <button onclick="physCookieAcceptAll()">
                <?php echo esc_html__('Accept All', 'phys-core'); ?>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php // Customise cookie consent
    $cookie_value = [];
    $analytics = $ads = $functional = '';

    if ( isset( $_COOKIE['physcookie-consent'] ) ) {
        $cookie_value = json_decode( wp_unslash( $_COOKIE['physcookie-consent'] ), true );
    }

    // Prepare the customise consent message
    $customise_consent_mess = $args['customise_consent_mess'];

    // Generate HTML for cookie categories
    $cookie_categories_html = [];
    foreach ($args['cookie_categories'] as $category_key => $category_data) {
        ob_start();
        ?>
            <div class="physcookie-cat cat-<?php echo esc_attr($category_key); ?>">
                <span style="font-size: 17px;" class="icon-toggle">+</span>

                <div class="header-cat">
                    <?php echo esc_html($category_data['title']); ?>
                    <?php if ($category_key === 'necessary') : ?>
                        <span class="note"><?php esc_html_e('Always Active', 'phys-core'); ?></span>
                    <?php else: ?>
                        <label><input type="checkbox" id="consent-<?php echo esc_attr($category_key); ?>" <?php echo (isset($cookie_value[$category_key]) && $cookie_value[$category_key] === 'yes') ? 'checked' : ''; ?>></label>
                    <?php endif; ?>
                </div>
                <p class="desc-for-cat">
                    <?php echo esc_html($category_data['desc']); ?>
                </p>
                <?php if (!empty($args['cookie_list'])) :
                    $cat_cookie_list = isset($args['cookie_list'][$category_key]) ? $args['cookie_list'][$category_key] : [];
                ?>
                    <div class="cklist-cat">
                        <?php if (!empty($cat_cookie_list)) : foreach ($cat_cookie_list as $cookie) : ?>
                            <ul class="cookie-info">
                                <li>
                                    <label><?php echo esc_html__('Cookie', 'phys-core'); ?></label>
                                    <span><?php echo esc_html($cookie['id']); ?></span>
                                </li>
                                <li>
                                    <label><?php echo esc_html__('Duration', 'phys-core'); ?></label>
                                    <span><?php echo esc_html($cookie['duration']); ?></span>
                                </li>
                                <li>
                                    <label><?php echo esc_html__('Description', 'phys-core'); ?></label>
                                    <span><?php echo esc_html($cookie['desc']); ?></span>
                                </li>
                            </ul>
                        <?php endforeach; else: ?>
                            <div class="empty-data" style="font-size: 14px;">
                                <?php echo esc_html__('No Cookie to display', 'phys-core'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
        $cookie_categories_html[$category_key] = ob_get_clean();
    }

    // Replace placeholders in the customise consent message
    foreach ($cookie_categories_html as $category_key => $category_html) {
        $customise_consent_mess = str_replace('{{' . $category_key . '}}', $category_html, $customise_consent_mess);
    }
?>

<div class="physcookie-customise phys-hide" id="physcookie-customise">
    <button class="phys-close-modal" title="<?php echo esc_attr('Close','phys-core');?>" onclick="physCloseModal()">X</button>
    
    <div class="customise-content">
        <div class="message">
            <?php echo $customise_consent_mess; ?>
        </div>
    </div>

    <div class="cookie-action">
        <button class="btn-outline" onclick="physCookieRejectAll()">
            <?php echo esc_html__('Reject All', 'phys-core'); ?>
        </button>
           
        <button class="btn-outline" onclick="savePhysConsent()">
            <?php echo esc_html__('Save My Preferences', 'phys-core'); ?>
        </button>

        <button onclick="physCookieAcceptAll()">
            <?php echo esc_html__('Accept All', 'phys-core'); ?>
        </button>
    </div>
</div>