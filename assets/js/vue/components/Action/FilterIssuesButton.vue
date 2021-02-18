<template>
  <button-with-modal-confirm
      :button-disabled="disabled" :title="$t('actions.filter_issues')"
      :confirm-title="$t('actions.set_filter')"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'filter']" class="pr-1" />
      {{ $t('filter._name') }}
    </template>

    <issue-filter-form
        :template="template" :maps="maps" :craftsmen="craftsmen"
        :view="view"
        @update="filter = $event" />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import IssueFilterForm from '../Form/IssueFilterForm'

export default {
  components: {
    IssueFilterForm,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      filter: null,
      patching: false
    }
  },
  props: {
    maps: {
      type: Array,
      default: []
    },
    craftsmen: {
      type: Array,
      default: []
    },
    view: {
      type: String,
      required: true
    },
    disabled: {
      type: Boolean,
      required: true
    },
    template: {
      type: Object,
      default: { }
    }
  },
  methods: {
    confirm: function () {
      this.$emit('update', this.filter)
    }
  }
}
</script>
