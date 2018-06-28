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
            no_craftsmen: "Keine Handwerker erfasst"
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