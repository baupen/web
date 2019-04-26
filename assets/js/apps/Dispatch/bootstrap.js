// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';

// components
import vueHeadful from 'vue-headful';

// app
import Dispatch from './dispatch';

// messages
Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('dispatch') != null) {
  // register plugins
  Vue.use(VueI18n);

  // register components
  Vue.component('vue-headful', vueHeadful);
  Vue.component('font-awesome-icon', FontAwesomeIcon);

  // initialize messages
  const i18n = new VueI18n({
    locale: document.documentElement.lang.substr(0, 2),
    messages: {}
  });

  // boot app
  // eslint-disable-next-line no-new
  new Vue({
    i18n,
    el: '#dispatch',
    template: '<Dispatch/>',
    components: { Dispatch }
  });
}
