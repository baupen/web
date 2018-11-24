// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// app
import Dashboard from './dashboard';

// messages
import Messages from '../../localization/dashboard';
import mergeMessages from '../../localization/shared/_all';

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('dashboard') != null) {
  // register plugins
  Vue.use(VueI18n);

  // register components
  Vue.component('font-awesome-icon', FontAwesomeIcon);

  // initialize messages
  const i18n = new VueI18n({
    locale: document.documentElement.lang.substr(0, 2),
    messages: mergeMessages(Messages)
  });

  // boot app
  // eslint-disable-next-line no-new
  new Vue({
    i18n,
    el: '#dashboard',
    template: '<Dashboard/>',
    components: { Dashboard }
  });
}
