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
    const toggleLine = function(element) {
        $('#line_' + element.attr('id'))
            .toggleClass('bg-light')
            .children(":first").toggleClass("shadow-sm");
    };
    const operationDetail = $('.accordion-body');
    operationDetail.on('show.bs.collapse', function() {
        toggleLine($(this));
    });
    operationDetail.on('hide.bs.collapse', function() {
        toggleLine($(this));
    });
};
