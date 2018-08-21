// vuejs & plugins
import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.config.productionTip = false;

// components
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import VueHeadful from 'vue-headful'

// app
import Craftsman from './craftsman'

// messages
import Messages from '../../../localization/share/craftsman'
import mergeMessages from '../../../localization/shared/_all'

// initialize app if html element is found
if (document.getElementById("share-craftsman") != null) {
    // share plugins
    Vue.use(VueI18n);

    // share components
    Vue.component('font-awesome-icon', FontAwesomeIcon);
    Vue.component('vue-headful', VueHeadful);


    // initialize messages
    const i18n = new VueI18n({
        locale: document.documentElement.lang.substr(0, 2),
        messages: mergeMessages(Messages),
    });

    // boot app
    new Vue({
        i18n,
        el: '#share-craftsman',
        template: '<Craftsman/>',
        components: {Craftsman}
    });
}