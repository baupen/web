// vuejs & plugins
import Vue from 'vue'
import VueI18n from 'vue-i18n'
Vue.config.productionTip = false;

// components
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

// app
import Register from './register'

// messages
import Messages from '../../localization/register'
import mergeMessages from '../../localization/shared/_all'

// initialize app if html element is found
if (document.getElementById("register") != null) {
    // register plugins
    Vue.use(VueI18n);

    // register components
    Vue.component('font-awesome-icon', FontAwesomeIcon);

    // initialize messages
    const i18n = new VueI18n({
        locale: document.documentElement.lang.substr(0, 2),
        messages: mergeMessages(Messages),
    });

    // add icons
    library.add(
        require('@fortawesome/pro-light-svg-icons/faCheck'),
        require('@fortawesome/pro-solid-svg-icons/faSortUp'),
        require('@fortawesome/pro-solid-svg-icons/faSortDown'),
        require('@fortawesome/pro-light-svg-icons/faSort'),
        require('@fortawesome/pro-solid-svg-icons/faStar'),
        require('@fortawesome/pro-light-svg-icons/faStar'),
        require('@fortawesome/pro-light-svg-icons/faTimes')
    );

    // boot app
    new Vue({
        i18n,
        el: '#register',
        template: '<Register/>',
        components: {Register}
    });
}