import Vue from 'vue'
import VueI18n from 'vue-i18n'
import BootstrapVue from 'bootstrap-vue'
import moment from 'moment'

import VueFlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  library as FontawesomeLibrary,
  config as FontawesomeConfig
} from '@fortawesome/fontawesome-svg-core'
import {
  faPlus,
  faPencil,
  faTrash,
  faUserAlt,
  faStar,
  faQuestionCircle,
  faEnvelopeOpen
} from '@fortawesome/pro-light-svg-icons'
import { faStar as faStartSolid } from '@fortawesome/pro-solid-svg-icons/faStar'
import '@fortawesome/fontawesome-svg-core/styles.css'

import { setInteractionMode, ValidationProvider, configure } from 'vee-validate'
import validateIt from 'vee-validate/dist/locale/it.json'
import validateDe from 'vee-validate/dist/locale/de.json'

import de from './localization/shared.de.json'
import it from './localization/shared.it.json'

import Switch from './vue/Switch.vue'
import Spinner from './vue/components/Spinner.vue'

// settings
const locale = document.documentElement.lang.substr(0, 2)

// configure fontawesome
FontawesomeConfig.autoAddCss = false
FontawesomeLibrary.add(
  faPlus,
  faPencil,
  faTrash,
  faUserAlt,
  faStar,
  faQuestionCircle,
  faEnvelopeOpen,

  faStartSolid
)

// configure moment
moment.locale('de')

// configure i18n
const i18n = new VueI18n({
  locale,
  messages: {
    de: {
      validations: validateDe,
      ...de
    },
    it: {
      validations: validateIt,
      ...it
    }
  }
})

// configure vee-validate
setInteractionMode('eager') // validate lazily first time, then aggressive
configure({
  // this will be used to generate messages.
  defaultMessage: (field, values) => {
    // eslint-disable-next-line no-param-reassign,no-underscore-dangle
    values._field_ = i18n.t(`fields.${field}`)
    // eslint-disable-next-line no-param-reassign,no-underscore-dangle
    return i18n.t(`validations.messages.${values._rule_}`, values)
  }
})

// configure vue
Vue.config.productionTip = false
Vue.use(VueI18n)
Vue.use(BootstrapVue)
Vue.use(VueFlatPickr)
Vue.component('FontAwesomeIcon', FontAwesomeIcon)
Vue.component('ValidationProvider', ValidationProvider)
Vue.component('Spinner', Spinner)

// boot apps
if (document.getElementById('switch') != null) {
  // eslint-disable-next-line no-new
  new Vue({
    i18n,
    el: '#switch',
    components: { App: Switch },
    render (h) {
      return h('App')
    }
  })
}
