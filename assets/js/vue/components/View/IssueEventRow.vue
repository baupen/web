<template>
  <div class="row" :class="{'bg-secondary-subtle': isContext, 'py-2': !last, 'pt-2': last}">
    <div class="col-auto">
      <p class="m-0 state-icon h-100" :class="'text-' + iconColor">
        <font-awesome-icon :icon="icon" :class="iconOpacity"/>
        <span class="state-joiner" v-if="!last"/>
      </p>
    </div>
    <div class="col">
      <p v-if="issueEvent.type === 'TEXT'" class="white-space-pre-line mb-0">
        {{ issueEvent.payload }}&nbsp;
      </p>
      <p v-else-if="issueEvent.type === 'STATUS_SET'" class="mb-0">
        <b>{{ $t('issue.state.' + issueEvent.payload.toLowerCase()) }}</b>
      </p>
      <p v-else-if="issueEvent.type === 'STATUS_UNSET'" class="mb-0">
        <b>
          <del>{{ $t('issue.state.' + issueEvent.payload.toLowerCase()) }}</del>
        </b>
      </p>
      <div v-else-if="issueEvent.type === 'IMAGE'">
        <image-lightbox :subject="issueEvent.payload" :src="issueEvent.fileUrl"/>
        <p v-if="issueEvent.payload" class="mb-0">
          {{ issueEvent.payload }}
        </p>
      </div>
      <p v-else-if="issueEvent.type === 'FILE'" class="mb-0">
        {{ issueEvent.payload }}
        <span v-if="issueEvent.payload">&nbsp;</span>
        <a :href="issueEvent.fileUrl" download class="text-nowrap">
          <font-awesome-icon :icon="['far', 'down']"/>
          {{ $t('_view.download') }}
        </a>
      </p>
      <p v-else-if="issueEvent.type === 'EMAIL'" class="mb-0">
        {{ emailPayload.subject }}
        <a v-if="!showEmailBody" href="" @click.prevent.stop="showEmailBody = true">
          {{ $t('_view.issue_event.show_email_body') }}
        </a>
        <span v-if="showEmailBody"
              class="d-block quote mt-1 mb-1 white-space-pre-line border-start border-2 border-secondary ps-2">
            {{ emailPayload.body }}
          </span>
      </p>
      <p class="text-secondary mb-0">
        <span class="d-inline-block text-black me-2">
          <strong v-if="isContext">{{ rootTypeName }}</strong>
          <span v-else>{{ createdByName }}</span>
        </span>
        <date-time-human-readable :value="issueEvent.timestamp"/>
      </p>
    </div>
    <div class="col-auto" v-if="canModify">
      <div class="btn-group">
        <edit-issue-event-button
            v-if="canEdit"
            :issue-event="issueEvent" :authority-iri="authorityIri" :created-by="createdBy"
            :last-changed-by="lastChangedBy"/>
        <remove-issue-event-button
            v-if="canDelete"
            :issue-event="issueEvent" @removed="$emit('removed')"/>
      </div>
    </div>
  </div>
</template>

<script>

import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import ImageLightbox from "./ImageLightbox.vue";
import ButtonWithModal from "../Library/Behaviour/ButtonWithModal.vue";
import RemoveIssueEventButton from "../Action/RemoveIssueEventButton.vue";
import EditIssueEventButton from "../Action/EditIssueEventButton.vue";
import {entityFormatter} from "../../services/formatters";
import {iriToId} from "../../services/api";

export default {
  emits: ['removed'],
  components: {
    EditIssueEventButton,
    RemoveIssueEventButton,
    ButtonWithModal,
    ImageLightbox,
    FontAwesomeIcon,
    DateTimeHumanReadable,
  },
  data() {
    return {
      showEmailBody: false,
    }
  },
  props: {
    issueEvent: {
      type: Object,
      required: true
    },
    authorityIri: {
      type: String,
      required: true
    },
    root: {
      type: Object,
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
    last: {
      type: Boolean,
      default: false
    },
    isContext: {
      type: Boolean,
      default: false
    },
  },
  computed: {
    rootTypeName: function () {
      if (this.root['@id'].includes('issues')) {
        return this.$t('issue._name')
      } else if (this.root['@id'].includes('craftsmen')) {
        return this.$t('craftsman._name')
      } else if (this.root['@id'].includes('construction_sites')) {
        return this.$t('construction_site._name')
      }
    },
    canModify: function () {
      return !this.isContext && ['TEXT', 'IMAGE', 'FILE'].includes(this.issueEvent.type)
    },
    canEdit: function () {
      if (this.authorityIri.includes("craftsmen")) {
        return this.createdBy['@id'] === this.authorityIri
      } else if (this.authorityIri.includes('construction_manager')) {
        return this.createdBy['@id'].includes('construction_managers')
      }
      return false
    },
    canDelete: function () {
      if (this.authorityIri.includes("craftsmen")) {
        return this.createdBy['@id'] === this.authorityIri
      } else if (this.authorityIri.includes('construction_manager')) {
        return true
      }
      return false
    },
    createdByName: function () {
      return this.createdBy ? entityFormatter.name(this.createdBy) : null
    },
    emailPayload: function () {
      return JSON.parse(this.issueEvent.payload)
    },
    icon: function () {
      if ('TEXT' === this.issueEvent.type) {
        if (this.root['@id'].includes('issues')) {
          return ['far', 'circle']
        } else if (this.root['@id'].includes('craftsmen')) {
          return ['far', 'circle-user']
        } else if (this.root['@id'].includes('construction_sites')) {
          return ['far', 'circle-o']
        }
      }
      if (['STATUS_SET', 'STATUS_UNSET'].includes(this.issueEvent.type)) {
        switch (this.issueEvent.payload) {
          case 'CREATED':
            return ['far', 'plus-circle']
          case 'REGISTERED':
            return ['far', 'dot-circle']
          case 'RESOLVED':
            return ['far', 'exclamation-circle']
          case 'CLOSED':
            return ['far', 'check-circle']
        }
      }
      if ('EMAIL' === this.issueEvent.type) {
        return ['far', 'circle-envelope']
      }
      if ('IMAGE' === this.issueEvent.type) {
        return ['far', 'circle-camera']
      }
      if ('FILE' === this.issueEvent.type) {
        return ['far', 'circle-down']
      }

      return []
    },
    iconOpacity: function () {
      if ('STATUS_UNSET' === this.issueEvent.type) {
        return 'opacity-25'
      }

      return ''
    },
    iconColor: function () {
      if (this.root['@id'].includes('issues')) {
        if (['STATUS_SET', 'STATUS_UNSET'].includes(this.issueEvent.type)) {
          switch (this.issueEvent.payload) {
            case 'CREATED':
            case 'REGISTERED':
              return 'primary'
            case 'RESOLVED':
              return 'orange'
            case 'CLOSED':
              return 'success'
          }
        }

        return 'primary'
      }

      return 'black'
    }
  }
}
</script>

<style scoped>
.state-icon {
  font-size: 2em;
  position: relative;
  top: -0.25em; /* align item with text */
  text-align: right;
}

.state-joiner {
  position: absolute;
  top: calc(2.4rem);
  height: calc(100% - 0.85rem);
  right: calc(0.5em - 1px);

  background-color: rgba(0, 0, 0, 0.1);
  width: 2px;
}

</style>

