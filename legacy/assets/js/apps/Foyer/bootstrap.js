// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';
import BootstrapVue from 'bootstrap-vue';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// app
import Foyer from './foyer';
import merge from 'deepmerge';

// messages
const sharedDe = require('../../localization/shared.de');
const sharedIt = require('../../localization/shared.it');
const customDe = require('../../localization/foyer.de');
const customIt = require('../../localization/foyer.it');

const translations = {
  de: merge(sharedDe, customDe),
  it: merge(sharedIt, customIt)
};

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('foyer') != null) {
  // register plugins
  Vue.use(VueI18n);
  Vue.use(BootstrapVue);

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
    el: '#foyer',
    components: { Foyer },
    render (h) {
      return h('Foyer');
    }
  });
}
