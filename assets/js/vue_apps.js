import Vue from 'vue'

Vue.config.productionTip = false;

// plugins
import VueI18n from 'vue-i18n'
import BootstrapVue from 'bootstrap-vue'
import Vuex from 'vuex'

Vue.use(VueI18n);
Vue.use(BootstrapVue);
Vue.use(Vuex);

// components
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {library} from '@fortawesome/fontawesome-svg-core'

Vue.component('font-awesome-icon', FontAwesomeIcon);

// initialize apps
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

    library.add(
        require('@fortawesome/fontawesome-pro-solid/faSortUp'),
        require('@fortawesome/fontawesome-pro-solid/faSortDown'),
        require('@fortawesome/fontawesome-pro-light/faSort')
    );

    new Vue({
        i18n: i18nConfirm,
        el: '#dispatch',
        template: '<Dispatch/>',
        components: {Dispatch}
    });
}