import { createApp } from 'vue'
import { createI18n } from 'vue-i18n'
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
import { faStar as faStarSolid } from '@fortawesome/pro-solid-svg-icons/faStar'
import '@fortawesome/fontawesome-svg-core/styles.css'

import de from './localization/de.json'
import it from './localization/it.json'

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
  faStarSolid
)

// configure moment
moment.locale(locale)

// configure i18n
const i18n = createI18n({
  locale,
  messages: { de, it }
})

// configure vue
const vue = createApp(Switch)

// vue.config.productionTip = false
vue.use(i18n)
vue.use(VueFlatPickr)
vue.component('FontAwesomeIcon', FontAwesomeIcon)
vue.component('Spinner', Spinner)

// boot apps
if (document.getElementById('switch') != null) {
  vue.mount('#switch')
}
