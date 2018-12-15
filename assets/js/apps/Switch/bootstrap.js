// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
// import ES6 style
import { VueMasonryPlugin } from 'vue-masonry';

// app
import Switch from './switch';

// messages
import Messages from '../../localization/switch';
import mergeMessages from '../../localization/shared/_all';

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('switch') != null) {
  // register plugins
  Vue.use(VueI18n);
  Vue.use(VueMasonryPlugin);

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
    el: '#switch',
    template: '<SwitchApp/>',
    components: { SwitchApp: Switch }
  });
}
