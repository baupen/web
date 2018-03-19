require("../sass/app.sass");
var $ = require("jquery");
var bootstrap = require("bootstrap");
var multiselect = require("bootstrap-multiselect/dist/js/bootstrap-multiselect.js");

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
    $('select[multiple]').multiselect({
        buttonClass: 'btn btn-secondary',
        templates: {
            ul: ' <ul class="multiselect-container dropdown-menu p-1 m-0"></ul>',
            li: '<li><a tabindex="0" class="dropdown-item"><label></label></a></li>'
        },
        buttonContainer: '<div class="dropdown" />'
    });
};


$(document).ready(function () {
    $("form").on("submit", disableFormButton);
    initializeSelects();
});