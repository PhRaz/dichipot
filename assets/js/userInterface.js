var $ = require('jquery');

module.exports = function () {

    /*
     * Disable submit and cancel buttons on submit and on cancel.
     */
    $("form").submit(function () {
        $("#submit").attr("disabled", true);
        $("#cancel").addClass("disabled");
    });
    $('#cancel').on('click', function() {
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

    /*
     * switch locale from home page selector
     */
    $('#locale-switch').change(function() {
        window.location.replace(window.location.protocol + "//" + window.location.host + "/" + $(this).val() + "/home");
    });
};
