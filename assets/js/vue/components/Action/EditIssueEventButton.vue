<template>
  <button-with-modal-confirm
      color="secondary"
      :button-disabled="patching" :title="$t('_action.edit_issue_event.title')"
      :confirm-title="$t('_action.save_changes')" :can-confirm="canConfirm"
      @confirm="confirm" >
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <issue-event-text-form @update="patch = $event" :template="issueEvent" :text-mode="issueEvent.type === 'TEXT'"/>
    <issue-event-meta-form @update="meta = $event" :template="issueEvent" :root="root"/>

    <hr/>

    <p class="mb-0 text-secondary">
      {{$t("issue_event.created_at")}}: <date-time-human-readable :value="issueEvent.createdAt" />
      ({{createdByName}})
      <br/>
      {{$t("issue_event.last_changed_at")}}: <date-time-human-readable :value="issueEvent.lastChangedAt" />
      ({{lastChangedByName}})
    </p>

  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import IssueEventTextForm from "../Form/IssueEventTextForm.vue";
import DateTimeHumanReadable from "../Library/View/DateTimeHumanReadable.vue";
import {entityFormatter} from "../../services/formatters";
import IssueEventMetaForm from "../Form/IssueEventMetaForm.vue";

export default {
  components: {
    IssueEventMetaForm,
    DateTimeHumanReadable,
    IssueEventTextForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      patch: null,
      meta: null,
      patching: false
    }
  },
  props: {
    issueEvent: {
      type: Object,
      required: true
    },
    root: {
      type: Object,
      required: true
    },
    authorityIri: {
      type: String,
      required: true
    },
    createdBy: {
      type: Object,
      required: false
    },
    lastChangedBy: {
      type: Object,
      required: false
    },
  },
  computed: {
    canConfirm: function () {
      return !!(this.patch && Object.keys(this.patch).length) ||
          !!(this.meta && Object.keys(this.meta).length)
    },
    createdByName: function () {
      return this.createdBy ? entityFormatter.name(this.createdBy) : null
    },
    lastChangedByName: function () {
      return this.lastChangedBy ? entityFormatter.name(this.lastChangedBy) : null
    },
  },
  methods: {
    confirm: function () {
      this.$emit('edit', this.patch)

      this.patching = true
      const payload = {
        lastChangedBy: iriToId(this.authorityIri),
        ...(this.patch ?? {}),
        ...(this.meta ?? {})
      }
      api.patch(this.issueEvent, payload, this.$t('_action.edit_issue_event.saved'))
          .then(_ => { this.patching = false})
    }
  }
}
</script>
