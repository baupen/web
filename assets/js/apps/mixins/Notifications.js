export default {
  methods: {
    displayInfoFlash: function (content) {
      this.displayFlash(content, 'success');
    },
    displayErrorFlash: function (content) {
      this.displayFlash(content, 'danger', 10000);
    },
    displayWarningFlash: function (content) {
      this.displayFlash(content, 'warning');
    },
    displayFlash: function (content, alertType, time = 2000) {
      // construct new alert html
      const alertTemplate = document.getElementById('alert-template');
      let alert = alertTemplate.innerHTML;
      const uniqueId = 'id-' + Math.random().toString(36).substr(2, 16);
      alert = alert.replace('ALERT_TYPE', alertType).replace('ID', uniqueId).replace('MESSAGE', content);

      // construct new element
      var alertElement = document.createElement('div');
      alertElement.innerHTML = alert;

      // append to document
      const flashWrapper = document.getElementsByClassName('flash-wrapper')[0];
      flashWrapper.appendChild(alertElement);

      // remove after timeout
      setTimeout(function () {
        flashWrapper.removeChild(alertElement);
      }, time);
    }
  }
};
