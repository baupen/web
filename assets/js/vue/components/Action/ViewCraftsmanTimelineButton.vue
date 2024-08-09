<template>
  <button-with-modal-confirm
      modal-size="mmd"
      :title="$t('_action.view_craftsman_timeline.title')">

    <template v-slot:footer>
      <span class="d-none"></span>
    </template>

    <template v-slot:button-content>
      <font-awesome-icon :icon="['far', 'list-ul']"/>
    </template>

    <div class="row">
      <div class="col-3">
        {{ $t('craftsman._name') }}
      </div>
      <div class="col">
        {{ craftsman.company }}<br/>
        <span class="text-muted">{{ craftsman.trade }}</span>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-3">
        {{ $t('craftsman._contact') }}
      </div>
      <div class="col">
        {{ craftsman.contactName }}<br/>
        <a :href="'mailto:'+craftsman.email">{{ craftsman.email }}</a>
        <template v-if="craftsman.emailCCs.length">
          <br/>
          {{ $t('craftsman.CCs') }}: {{ craftsman.emailCCs.join(", ") }}
        </template>
      </div>
    </div>

    <hr/>

    <craftsman-timeline
        :construction-site="constructionSite" :craftsman="craftsman"
        :construction-managers="constructionManagers" :authority-iri="constructionManagerIri"
    />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanTimeline from "../View/CraftsmanTimeline.vue";

export default {
  components: {
    CraftsmanTimeline,
    ButtonWithModalConfirm
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    },
    craftsman: {
      type: Object,
      required: true,
    },
    constructionManagers: {
      type: Array,
      required: true
    },
  }
}
</script>

