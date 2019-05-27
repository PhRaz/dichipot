var $ = require('jquery');

module.exports = function () {
    /*
     * Disable submit button on submit.
     */
    $("form").submit(function () {
        $("#submit").attr("disabled", true);
        $("#cancel").addClass("disabled");
    });

    /*
     * Toggle color and shadow on operation accordion
     */
    var toggleLine = function(element) {
        $('#line_' + $(this).attr('id'))
            .toggleClass('bg-light')
            .children(":first").toggleClass("shadow-sm");
    };
    $('.accordion-body').on('show.bs.collapse', function() {
        toggleLine($(this));
    });
    $('.accordion-body').on('hidden.bs.collapse', function() {
        toggleLine($(this));
    });
};