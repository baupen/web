export default {
    methods: {
        displayInfoFlash: function (content) {
            this.displayFlash(content, "success");
        },
        displayErrorFlash: function (content) {
            this.displayFlash(content, "danger", 10000);
        },
        displayWarningFlash: function (content) {
            this.displayFlash(content, "warning");
        },
        displayFlash: function (content, alertType, time = 2000) {
            let alert = $('#alert-template').html();
            const uniqueId = 'id-' + Math.random().toString(36).substr(2, 16);
            alert = alert.replace("ALERT_TYPE", alertType).replace("ID", uniqueId).replace("MESSAGE", content);

            $('.flash-wrapper').append(alert);
            $('#' + uniqueId).alert();

            setTimeout(function () {
                $('#' + uniqueId).alert('close');
            }, time);
        }
    }
}