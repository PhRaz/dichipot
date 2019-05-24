/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('../css/app.css');
require('../css/global.scss');

const $ = require('jquery');
require('bootstrap');

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

var eventAddUser = require('./eventAddUser');
var submit = require('./submit');

$(document).ready(function () {
    eventAddUser();
    submit();
});