var $ = require('jquery');

/*
 * Disable submit button on submit.
 */
module.exports = function () {
    $("form").submit(function () {
        $("#submit").attr("disabled", true);
        $("#cancel").addClass("disabled");
    });
};