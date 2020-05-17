// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';
import BootstrapVue from 'bootstrap-vue';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import VueHeadful from 'vue-headful';

// app
import Craftsman from './craftsman';
import merge from 'deepmerge';

// messages
const sharedDe = require('../../../localization/shared.de');
const sharedIt = require('../../../localization/shared.it');
const customDe = require('../../../localization/share/craftsman.de');
const customIt = require('../../../localization/share/craftsman.it');

const translations = {
  de: merge(sharedDe, customDe),
  it: merge(sharedIt, customIt)
};

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('share-craftsman') != null) {
  // share plugins
  Vue.use(VueI18n);
  Vue.use(BootstrapVue);

  // share components
  Vue.component('font-awesome-icon', FontAwesomeIcon);
  Vue.component('vue-headful', VueHeadful);

  // initialize messages
  const i18n = new VueI18n({
    locale: document.documentElement.lang.substr(0, 2),
    messages: translations
  });

  // boot app
  // eslint-disable-next-line no-new
  new Vue({
    i18n,
    el: '#share-craftsman',
    components: { Craftsman },
    render (h) {
      return h('Craftsman');
    }
  });
}
