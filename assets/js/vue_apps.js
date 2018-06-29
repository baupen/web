import Vue from 'vue'
import VueI18n from 'vue-i18n'
import BootstrapVue from 'bootstrap-vue'
import Vuex from 'vuex'

Vue.config.productionTip = false;

// translations
Vue.use(VueI18n);
Vue.use(BootstrapVue);
Vue.use(Vuex);

//confirm app
import Dispatch from './apps/dispatch/dispatch'

if (document.getElementById("dispatch") != null) {
    const messagesDispatch = {
        de: {
            no_craftsmen: "Keine Handwerker erfasst",
            send_emails: "Emails versenden",
            craftsman: {
                name: "Name",
                trade: "Funktion",
                not_responded_issues_count: "Unbeantwortet Pendenzen",
                not_read_issues_count: "Ungelesene Pendenzen",
                next_response_limit: "Nächste Limite",
                last_email_sent: "Letzte versandte E-Mail",
                last_online_visit: "Letzter Webseitenbesuch"
            }
        }
    };

    const i18nConfirm = new VueI18n({
        locale: 'de',
        messages: messagesDispatch,
    });

    new Vue({
        i18n: i18nConfirm,
        el: '#dispatch',
        template: '<Dispatch/>',
        components: {Dispatch}
    });
}