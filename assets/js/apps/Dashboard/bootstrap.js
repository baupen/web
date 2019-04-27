// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// app
import Dashboard from './dashboard';

// messages
import merge from 'deepmerge';

// messages
const sharedDe = require('../../localization/shared.de');
const sharedIt = require('../../localization/shared.it');
const customDe = require('../../localization/dashboard.de');
const customIt = require('../../localization/dashboard.it');

const translations = {
  de: merge(sharedDe, customDe),
  it: merge(sharedIt, customIt)
};

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
    messages: translations
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
