
var $ = require('jquery');

// required by bootstrap
require('popper.js');

// JS is equivalent to the normal "bootstrap" package
// no need to set this to a variable, just require it
require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
