// vuejs & plugins
import Vue from 'vue';
import VueI18n from 'vue-i18n';

// components
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Plugin } from 'vue-fragment';

// app
import Edit from './edit';

// messages
import Messages from '../../localization/edit';
import mergeMessages from '../../localization/shared/_all';
import Vuelidate from 'vuelidate';

Vue.config.productionTip = false;

// initialize app if html element is found
if (document.getElementById('edit') != null) {
  // register plugins
  Vue.use(VueI18n);
  Vue.use(Plugin);
  Vue.use(Vuelidate);

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
    el: '#edit',
    template: '<EditApp/>',
    components: { EditApp: Edit }
  });
}
