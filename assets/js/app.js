require("../sass/app.sass");
var $ = require("jquery");
var bootstrap = require("bootstrap");
var multiselect = require("bootstra");

window.$ = $;

//prevent double submit & give user instant feedback
var disableFormButton = function () {
    var $form = $(this);
    var $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

var initializeSelects = function () {
    $('select').multiselect({
        buttonClass: 'btn btn-secondary',
        templates: {
            li: '<li><a tabindex="0" class="dropdown-item"><label></label></a></li>'
        }
    });
};


$(document).ready(function () {
    $("form").on("submit", disableFormButton);
    initializeSelects();
});