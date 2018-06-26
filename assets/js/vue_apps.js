import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.config.productionTip = false;

// translations
Vue.use(VueI18n);

//confirm app
import ConfirmApp from './apps/confirm/confirm'

if (document.getElementById("communication-app") != null) {
    const messagesCofirm = {
        de: {
            confirm_events: "Termine bestätigen",
            no_user_assigned: "Keinem Mitarbeiter zugewiesen"
        }
    };

    const i18nConfirm = new VueI18n({
        locale: 'de',
        messages: messagesCofirm,
    });

    new Vue({
        i18n: i18nConfirm,
        el: '#confirm-app',
        template: '<ConfirmApp/>',
        components: {ConfirmApp}
    });
}