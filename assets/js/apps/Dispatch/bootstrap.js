// vuejs & plugins
import Vue from 'vue'
import VueI18n from 'vue-i18n'
Vue.config.productionTip = false;

// components
import vueHeadful from 'vue-headful';

// app
import Dispatch from './dispatch'

// messages
import Messages from '../../localization/dispatch'
import mergeMessages from '../../localization/shared/_all'

// initialize app if html element is found
if (document.getElementById("dispatch") != null) {
    // register plugins
    Vue.use(VueI18n);

    // register components
    Vue.component('vue-headful', vueHeadful);

    // initialize messages
    const i18n = new VueI18n({
        locale: document.documentElement.lang.substr(0, 2),
        messages: mergeMessages(Messages),
    });

    // boot app
    new Vue({
        i18n,
        el: '#dispatch',
        template: '<Dispatch/>',
        components: {Dispatch}
    });
}