import Vue from 'vue'

Vue.config.productionTip = false;

// plugins
import VueI18n from 'vue-i18n'
import BootstrapVue from 'bootstrap-vue'
import Vuex from 'vuex'
import Messages from './localization/dispatch'

Vue.use(VueI18n);
Vue.use(BootstrapVue);
Vue.use(Vuex);

// components
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import {library} from '@fortawesome/fontawesome-svg-core'

Vue.component('font-awesome-icon', FontAwesomeIcon);

// initialize apps
import Dispatch from './apps/dispatch'

if (document.getElementById("dispatch") != null) {
    const messagesDispatch = Messages;

    const lang = document.documentElement.lang.substr(0, 2);
    const i18nConfirm = new VueI18n({
        locale: lang,
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