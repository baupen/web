<template>
  <div class="row pt-1 pb-1" :class="{'bg-secondary-subtle': isContext}">
    <div class="col-3">
      <p class="m-0 state-icon h-100" :class="'text-' + iconColor">
        <font-awesome-icon :icon="icon" :class="iconOpacity"/>
        <span class="state-joiner" v-if="!last"/>
      </p>
    </div>
    <div class="col">
      <div class="mt-1">
        <p v-if="protocolEntry.type === 'TEXT'" class="white-space-pre-line mb-0">
          {{ protocolEntry.payload }}&nbsp;
        </p>
        <p v-else-if="protocolEntry.type === 'STATUS_SET'" class="mb-0">
          <b>{{ $t('issue.state.' + protocolEntry.payload.toLowerCase()) }}</b>
        </p>
        <p v-else-if="protocolEntry.type === 'STATUS_UNSET'" class="mb-0">
          <b>
            <del>{{ $t('issue.state.' + protocolEntry.payload.toLowerCase()) }}</del>
          </b>
        </p>
        <div v-else-if="protocolEntry.type === 'IMAGE'">
          <image-lightbox :subject="protocolEntry.payload" :src="protocolEntry.fileUrl"/>
          <p v-if="protocolEntry.payload" class="mb-0">
            {{ protocolEntry.payload }}
          </p>
        </div>
        <p v-else-if="protocolEntry.type === 'FILE'" class="mb-0">
          {{ protocolEntry.payload }}&nbsp;
          <a :href="protocolEntry.fileUrl" download>
            <font-awesome-icon :icon="['far', 'down']"/>
            {{ $t('_view.download') }}
          </a>
        </p>
        <p v-else-if="protocolEntry.type === 'EMAIL'" class="mb-0">
          {{ emailPayload.subject }}
          <a v-if="!showEmailBody" href="" @click.prevent.stop="showEmailBody = true">
            {{ $t('_view.protocol_entry.show_email_body') }}
          </a>
          <span v-if="showEmailBody" class="d-block quote mt-1 mb-1 white-space-pre-line border-start border-2 border-secondary ps-2">
            {{ emailPayload.body }}
          </span>
        </p>
      </div>
      <p class="text-secondary mb-0">
        <span v-if="isContext">
          {{ rootTypeName + ", " }}
        </span>
        <date-time-human-readable :value="protocolEntry.createdAt"/>
        <span>, </span>
        {{ createdByName }}
      </p>
    </div>
    <div class="col-auto" v-if="isRemovable">
      <remove-protocol-entry-button :protocol-entry="protocolEntry" @removed="$emit('removed')"/>
    </div>
  </div>
</template>

<script>

import {entityFormatter} from '../../services/formatters'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import ImageLightbox from "./ImageLightbox.vue";
import ButtonWithModal from "../Library/Behaviour/ButtonWithModal.vue";
import RemoveProtocolEntryButton from "../Action/RemoveProtocolEntryButton.vue";

export default {
  emits: ['removed'],
  components: {
    RemoveProtocolEntryButton,
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
    protocolEntry: {
      type: Object,
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
    last: {
      type: Boolean,
      default: false
    },
    isContext: {
      type: Boolean,
      default: false
    },
    isRemovable: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    createdByName: function () {
      return this.createdBy ? entityFormatter.name(this.createdBy) : null
    },
    rootTypeName: function () {
      if (this.root['@id'].includes('issues')) {
        return this.$t('issue._name')
      } else if (this.root['@id'].includes('craftsmen')) {
        return this.$t('craftsman._name')
      } else if (this.root['@id'].includes('construction_sites')) {
        return this.$t('construction_site._name')
      }
    },
    emailPayload: function () {
      return JSON.parse(this.protocolEntry.payload)
    },
    icon: function () {
      if ('TEXT' === this.protocolEntry.type) {
        if (this.root['@id'].includes('issues')) {
          return ['far', 'circle']
        } else if (this.root['@id'].includes('craftsmen')) {
          return ['far', 'circle-user']
        } else if (this.root['@id'].includes('construction_sites')) {
          return ['far', 'circle-o']
        }
      }
      if (['STATUS_SET', 'STATUS_UNSET'].includes(this.protocolEntry.type)) {
        switch (this.protocolEntry.payload) {
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
      if ('EMAIL' === this.protocolEntry.type) {
        return ['far', 'circle-envelope']
      }
      if ('IMAGE' === this.protocolEntry.type) {
        return ['far', 'circle-camera']
      }
      if ('FILE' === this.protocolEntry.type) {
        return ['far', 'circle-down']
      }

      return []
    },
    iconOpacity: function () {
      if ('STATUS_UNSET' === this.protocolEntry.type) {
        return 'opacity-25'
      }

      return ''
    },
    iconColor: function () {
      if (this.root['@id'].includes('issues')) {
        if (['STATUS_SET', 'STATUS_UNSET'].includes(this.protocolEntry.type)) {
          switch (this.protocolEntry.payload) {
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
  text-align: right;
}

.state-joiner {
  position: absolute;
  top: calc(2.4rem);
  height: calc(100% - 1.4rem);
  right: calc(0.5em - 1px);

  background-color: rgba(0, 0, 0, 0.1);
  width: 2px;
}

</style>

