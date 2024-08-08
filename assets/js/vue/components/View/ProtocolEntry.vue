<template>
  <div class="row mb-3">
    <div class="col-md-3">
      <p class="m-0 state-icon h-100" :class="'text-' + iconColor">
        <font-awesome-icon :icon="icon"/>
        <span class="state-joiner" v-if="!last"/>
      </p>
    </div>
    <div class="col">
      <div class="mt-1">
        <span v-if="protocolEntry.type === 'TEXT'" class="white-space-pre-line">
          {{ protocolEntry.payload }}&nbsp;
        </span>
        <span v-else-if="protocolEntry.type === 'STATUS_SET'">
          <b>{{ $t('issue.state.' + protocolEntry.payload.toLowerCase()) }}</b>
        </span>
        <span v-else-if="protocolEntry.type === 'STATUS_UNSET'">
          <b>
            <del>{{ $t('issue.state.' + protocolEntry.payload.toLowerCase()) }}</del>
          </b>
        </span>
      </div>
      <p class="text-secondary mb-0">
        <date-time-human-readable :value="protocolEntry.createdAt"/>,
        {{ createdByName }}
      </p>
    </div>
  </div>
</template>

<script>

import {entityFormatter} from '../../services/formatters'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'

export default {
  components: {
    DateTimeHumanReadable,
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
    }
  },
  computed: {
    createdByName: function () {
      return this.createdBy ? entityFormatter.name(this.createdBy) : null
    },
    icon: function () {
      if ('TEXT' === this.protocolEntry.type) {
        if (this.root['@id'].includes('issue')) {
          return ['far', 'circle']
        } else if (this.root['@id'].includes('craftsman')) {
          return ['far', 'circle-user']
        } else if (this.root['@id'].includes('construction_side')) {
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
    iconColor: function () {
      if ('STATUS_SET' === this.protocolEntry.type) {
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

      if ('STATUS_UNSET' === this.protocolEntry.type) {
        return 'secondary'
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
  height: calc(100% - 1rem);
  right: calc(0.5em - 1px);

  background-color: rgba(0, 0, 0, 0.1);
  width: 2px;
}
</style>

