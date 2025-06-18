;(function($) {

    $(document).ready(function() {
        phys_open_pointer(0);
        function phys_open_pointer(i) {
            var pointer = phys_pointers.pointers[i];
            var options = $.extend( pointer.options, {
                close: function() {
                    $.post( ajaxurl, {
                        pointer: pointer.pointer_id,
                        action: 'dismiss-wp-pointer'
                    });
                }
            });

            $(pointer.target).pointer( options ).pointer('open');
        }
    });
})(jQuery);
