(function ($) {
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + encodeURIComponent(JSON.stringify(value)) + expires + "; path=/";

        // Call removeBlockedElements after setting the cookie
        if (name === "physcookie-consent") {
            removeBlockedElements();
        }
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) {
            try {
                return JSON.parse(decodeURIComponent(match[2]));
            } catch (e) {
                return null;
            }
        }
        return null;
    }

    function removeBlockedElements() {
        const consent = getCookie("physcookie-consent");
        if (!consent) return;
    
        // Fetch cookie list data from the server (via AJAX or inline script)
        const cookieList = window.physCookieList || {};
    
        // Iterate over the keys of the cookieList object
        Object.keys(cookieList).forEach(function (category) {
            const cookies = cookieList[category];
    
            // Check if the consent for this category is "no"
            if (consent[category] === "no" && Array.isArray(cookies)) {
                cookies.forEach(function (cookie) {
                    const src = cookie.src;
    
                    if (src) {
                        // Remove scripts with the specified src
                        document.querySelectorAll(`script[src="${src}"]`).forEach(function (el) {
                            el.remove(); // Remove blocked scripts
                        });
                    }
                });
            }
        });
    
        // Block dynamically added scripts
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeName.toLowerCase() === "script") {
                        const src = node.src || node.getAttribute('src');
                        Object.keys(cookieList).forEach(function (category) {
                            const cookies = cookieList[category];
                            if (consent[category] === "no" && Array.isArray(cookies)) {
                                cookies.forEach(function (cookie) {
                                    if (src === cookie.src) {
                                        node.remove(); // Remove blocked scripts
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
    
        observer.observe(document.body, { childList: true, subtree: true });
    }

    window.physCustomise = function () {
        var customiseModal = $('#physcookie-customise');
        var mdoverlay = $('.md-overlay');
        var banner = $('#physcookie-banner');
    
        if (customiseModal.length) {
            // Show the customise modal
            customiseModal.removeClass('phys-hide');
            mdoverlay.removeClass('phys-hide');
    
            // Hide the cookie banner if it is visible
            if (banner.length && !banner.hasClass('phys-hide')) {
                banner.addClass('phys-hide');
            }
        }
    };

    window.physCloseModal = function () {
        var customiseModal = $('#physcookie-customise');
        var mdoverlay = $('.md-overlay');
        var banner = $('#physcookie-banner');
    
        if (customiseModal.length) {
            // Hide the customise modal
            customiseModal.addClass('phys-hide');
            mdoverlay.addClass('phys-hide');
    
            // Show the cookie banner if it is not visible
            if (banner.length && banner.hasClass('phys-hide')) {
                banner.removeClass('phys-hide');
            }
        }
    };

    window.physCookieAcceptAll = function () {
        const consent = {
            necessary: "yes",
            analytics: "yes",
            ads: "yes",
            functional: "yes"
        };

        setCookie("physcookie-consent", consent, 365);
        location.reload();
    };

    window.physCookieRejectAll = function () {
        const consent = {
            necessary: "yes", // Necessary cookies cannot be rejected
            analytics: "no",
            ads: "no",
            functional: "no"
        };
        
        setCookie("physcookie-consent", consent, 365);
        // location.reload();
    };

    window.savePhysConsent = function () {
        const consent = {
            necessary: "yes",
            analytics: document.getElementById('consent-analytics')?.checked ? "yes" : "no",
            ads: document.getElementById('consent-ads')?.checked ? "yes" : "no",
            functional: document.getElementById('consent-functional')?.checked ? "yes" : "no"
        };
        setCookie("physcookie-consent", consent, 365);
        location.reload();
    };

    $(document).ready(function () {
        // Run removeBlockedElements on page load
        removeBlockedElements();

        // cookie consent control
        const consent = getCookie("physcookie-consent");
        if (consent) {
            document.querySelectorAll('[type="text/plain"][data-physcookie-category]').forEach(function (el) {
                const cat = el.getAttribute('data-physcookie-category');
                if (consent[cat] === "yes") {
                    const s = document.createElement('script');
                    if (el.src) {
                        s.src = el.src;
                    } else {
                        s.textContent = el.textContent;
                    }
                    document.body.appendChild(s);
                }
            });
        }

        // Prevent checkbox click from toggling the parent .physcookie-cat
        $('#physcookie-customise .physcookie-cat input[type="checkbox"]').on('click', function (e) {
            e.stopPropagation();
        });

        // Toggle functionality for .physcookie-cat
        $('#physcookie-customise .physcookie-cat').on('click', function () {
            const toggleIcon = $(this).find('.icon-toggle');
        
            // Toggle the 'toggled' class
            $(this).toggleClass('toggled');
        
            // Update the text content of .icon-toggle
            if ($(this).hasClass('toggled')) {
                toggleIcon.text('-');
            } else {
                toggleIcon.text('+');
            }
        });
      
    });
})(jQuery);
