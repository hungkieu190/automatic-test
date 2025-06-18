jQuery(document).ready(function ($) {
    // On change cookie category: action update_cookie_manager_form
    $('#cookie_category').on('change', function () {
        const selectedCategory = $(this).val();

        // Update the URL parameter
        const url = new URL(window.location.href);
        url.searchParams.set('cookie-category', selectedCategory);
        window.history.replaceState(null, '', url.toString());
    
        // Perform AJAX request to fetch updated form content
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_cookie_manager_form',
                cookie_category: selectedCategory, 
            },
            success: function (response) {
                if (response.success) {
                    $('#cookie-manager-form').html(response.data.form_html);
                } else {
                    console.error(response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
            },
        });
    });
    

    // Submit form: action cookie_consent_settings
    $('.cookie-consent-form').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        // Convert serialized form data into an object
        const formDataObject = {};
        formData.split('&').forEach(function (item) {
            const [key, value] = item.split('=');
            formDataObject[decodeURIComponent(key)] = decodeURIComponent(value);
        });

        // Add the action and nonce to the form data object
        formDataObject.action = 'cookie_consent_settings';
        formDataObject.cookie_consent_nonce = physCookieConsent.nonce;

        // Include HTML content from wp_editor (if applicable)
        const editorContent = tinyMCE.get('consent_message')?.getContent() || $('#consent_message').val();
        const editorContentMess = tinyMCE.get('customise_consent_mess')?.getContent() || $('#customise_consent_mess').val();
        formDataObject.customise_consent_mess = editorContentMess;
        formDataObject.consent_message = editorContent;

        // Send the AJAX request
        $.post(ajaxurl, formDataObject)
            .done(function (response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    location.reload();
                }
            })
            .fail(function () {
                alert('Failed to save settings.');
            });
    });

    
    // Delete cookie list item: action phys_edit_cookie_list
    window.physEditCookieList = function (cookie_id, cookie_category) {
        if (!confirm('Are you sure you want to delete this cookie?')) {
            return;
        }

        // Perform AJAX request to delete the cookie
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'phys_edit_cookie_list',
                cookie_id: cookie_id,
                cookie_category: cookie_category,
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || 'Failed to delete cookie.');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Failed to delete cookie.');
            },
        });
    };

    // Render cookie details in the table
    function renderCookieTable() {
        const tableBody = $('#cookie-scan-list-table tbody');
        tableBody.empty(); 

        if (physCookieConsent.cookieDetails.length > 0) {
            physCookieConsent.cookieDetails.forEach(function (cookie) {
                const row = `
                    <tr>
                        <td>${cookie.name}</td>
                        <td>${cookie.domain}</td>
                        <td>${cookie.description}</td>
                        <td>${cookie.duration}</td>
                        <td>${cookie.type}</td>
                    </tr>
                `;
                tableBody.append(row);
            });
        } else {
            tableBody.append('<tr><td colspan="5">No cookies found.</td></tr>');
        }
    }
    renderCookieTable();
    
});