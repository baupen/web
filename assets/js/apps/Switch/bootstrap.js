// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';
import BootstrapVue from 'bootstrap-vue';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import Vuelidate from 'vuelidate';

// import ES6 style
import { VueMasonryPlugin } from 'vue-masonry';

// app
import Switch from './switch';

// messages
import merge from 'deepmerge';

const sharedDe = require('../../localization/shared.de');
const sharedIt = require('../../localization/shared.it');
const customDe = require('../../localization/switch.de');
const customIt = require('../../localization/switch.it');

const translations = {
  de: merge(sharedDe, customDe),
  it: merge(sharedIt, customIt)
};

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('switch') != null) {
  // register plugins
  Vue.use(VueI18n);
  Vue.use(BootstrapVue);
  Vue.use(VueMasonryPlugin);
  Vue.use(Vuelidate);

  // register components
  Vue.component('font-awesome-icon', FontAwesomeIcon);

  // initialize messages
  const i18n = new VueI18n({
    locale: document.documentElement.lang.substr(0, 2),
    messages: translations
  });

  // boot app
  // eslint-disable-next-line no-new
  new Vue({
    i18n,
    el: '#switch',
    components: { Switch },
    render (h) {
      return h('Switch');
    }
  });
}
