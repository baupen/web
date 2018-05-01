require("../sass/app.sass");
const $ = require("jquery");
const bootstrap = require("bootstrap");
const multiselect = require("bootstrap-multiselect/dist/js/bootstrap-multiselect.js");
const ekkoLightbox = require("ekko-lightbox");
const dataTable = require("datatables.net-bs4");

window.$ = $;
window.ekkoLightbox = ekkoLightbox;

FontAwesomeConfig = { autoAddCss: false };

//icons
const fontawesome = require('@fortawesome/fontawesome');
fontawesome.library.add(
    require('@fortawesome/fontawesome-pro-light/faBuilding'),
    require('@fortawesome/fontawesome-pro-light/faGavel'),
    require('@fortawesome/fontawesome-pro-light/faUser')
);


//prevent double submit & give user instant feedback
const disableFormButton = function () {
    const $form = $(this);
    const $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

const initializeSelects = function () {
    $('select[multiple]').multiselect({
        buttonClass: 'btn btn-secondary',
        templates: {
            ul: ' <ul class="multiselect-container dropdown-menu p-1 m-0"></ul>',
            li: '<li><a tabindex="0" class="dropdown-item"><label></label></a></li>'
        },
        buttonContainer: '<div class="dropdown" />',
        nonSelectedText: 'Nichts ausgewählt',
        nSelectedText: 'ausgewählt',
        allSelectedText: 'Alle ausgewählt'
    });
};

const initializeLightbox = function (event) {
    event.preventDefault();
    $(this).ekkoLightbox();
};

const initializeAjax = function (event) {
    event.preventDefault();
    const $form = $(this);
    const url = $form.attr("action");

    $.ajax({
        type: "POST",
        url: url,
        data: $form.serialize(), // serializes the form's elements.
        success: function (data) {
            const $buttons = $(".btn", $form);
            $buttons.removeClass("disabled");
        }
    });
};


$(document).ready(function () {

    $("form").on("submit", disableFormButton);
    $("a[data-toggle=lightbox]").on('click', initializeLightbox);
    initializeSelects();

    $(".print-button").on("click", function (e) {
        console.log("printing")
        window.print()
    });

    const url = window.location.href;
    const endOfUrl = url.substr(url.lastIndexOf('/') + 1);

    if (endOfUrl === "print") {
        window.print();
    }


    //force reload on user browser button navigation
    $(window).on('popstate', function () {
        location.reload(true);
    });

    $('form.ajax-form').on("submit", initializeAjax);

    /* Default class modification */
    $.extend(dataTable.ext.classes, {
        sWrapper: "dataTables_wrapper dt-bootstrap4"
    });

    $('.data-table').dataTable(
        {
            language: {
                emptyTable: "Keine Daten in der Tabelle vorhanden",
                info: "_START_ bis _END_ von _TOTAL_ Einträgen",
                infoEmpty: "0 bis 0 von 0 Einträgen",
                infoFiltered: "(gefiltert von _MAX_ Einträgen)",
                infoPostFix: "",
                infoThousands: ".",
                lengthMenu: "_MENU_ Einträge anzeigen",
                loadingRecords: "Wird geladen...",
                processing: "Bitte warten...",
                search: "Suchen",
                zeroRecords: "Keine Einträge vorhanden.",
                paginate: {
                    first: "Erste",
                    previous: "Zurück",
                    next: "Nächste",
                    last: "Letzte"
                },
                aria: {
                    sortAscending: ": aktivieren, um Spalte aufsteigend zu sortieren",
                    sortDescending: ": aktivieren, um Spalte absteigend zu sortieren"
                },
                select: {
                    rows: {
                        _: '%d Zeilen ausgewählt',
                        0: 'Zum Auswählen auf eine Zeile klicken',
                        1: '1 Zeile ausgewählt'
                    }
                }
            }
        }
    );
});

