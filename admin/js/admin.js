(function ($) {
    'use strict';
    document.addEventListener("DOMContentLoaded", function () {
        // Make the container sortable.
        $("#sortable-fields").sortable({
            placeholder: "ui-state-highlight",
            handle: ".field-header",  // drag handle is the header
            update: function (event, ui) {
                // console.log("New field order:");
                $('#sortable-fields .field-item').each(function (index) {
                    $(this).find('.field-order').val(index);
                });
            }
        });
        $("#sortable-fields").disableSelection();

        // Initialize accordion for each accordion-container.
        $(".accordion-container").accordion({
            collapsible: true,
            active: false,
            heightStyle: "content"
        });
    });
})(jQuery);