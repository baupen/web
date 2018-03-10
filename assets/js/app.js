require("../sass/app.sass");
var $ = require("jquery");
var bootstrap = require("bootstrap");

window.$ = $;

//prevent double submit & give user instant feedback
var disableFormButton = function () {
    var $form = $(this);
    var $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

$(document).ready(function () {
    $("form").on("submit", disableFormButton);
});