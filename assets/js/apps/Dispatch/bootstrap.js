// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';
import BootstrapVue from 'bootstrap-vue';

// components
import vueHeadful from 'vue-headful';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// app
import Dispatch from './dispatch';

// messages
import merge from 'deepmerge';

// messages
const sharedDe = require('../../localization/shared.de');
const sharedIt = require('../../localization/shared.it');
const customDe = require('../../localization/dispatch.de');
const customIt = require('../../localization/dispatch.it');

const translations = {
  de: merge(sharedDe, customDe),
  it: merge(sharedIt, customIt)
};

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('dispatch') != null) {
  // register plugins
  Vue.use(VueI18n);
  Vue.use(BootstrapVue);

  // register components
  Vue.component('vue-headful', vueHeadful);
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
    el: '#dispatch',
    template: '<Dispatch/>',
    components: { Dispatch }
  });
}
