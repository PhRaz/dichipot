var $ = require('jquery');

/*
 * Select field content to change their value without requiring character deletion
 */
module.exports = function () {
    $('input:text')
        .filter(function () {
            console.log("filter");
            return this.id.match(/operation_expenses_/);
        })
        .focus(function () {
            $(this).select();
        });
};