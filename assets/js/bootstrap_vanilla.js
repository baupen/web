// jquery & attach to window
const $ = require('jquery');
window.$ = $;

// include bootstrap
const bootstrap = require('bootstrap');
window.bootstrap = bootstrap;

// register some basic usability functionality
$(document).ready(function () {
    // give use instant feedback on form submission
    $('form').on('submit', function () {
        const $form = $(this);
        const $buttons = $('.btn', $form);
        if (!$buttons.hasClass('no-disable')) {
            $buttons.addClass('disabled');
        }
    });

    // force reload on user browser button navigation
    $(window).on('popstate', function () {
        window.location.reload(true);
    });
});
