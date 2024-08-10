<template>
  <custom-checkbox-field
      for-id="filter-is-marked" :label="$t('issue.is_marked')"
      :show-reset="filter.isMarked !== null" @reset="filter.isMarked = null">
    <input
        class="form-check-input" type="checkbox" id="filter-is-marked"
        v-model="filter.isMarked"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="filter.isMarked === null"
    >
  </custom-checkbox-field>

  <custom-checkbox-field
      for-id="filter-was-added-with-client" :label="$t('issue.was_added_with_client')"
      :show-reset="filter.wasAddedWithClient !== null" @reset="filter.wasAddedWithClient = null">
    <input
        class="form-check-input" type="checkbox" id="filter-was-added-with-client"
        v-model="filter.wasAddedWithClient"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="filter.wasAddedWithClient === null"
    >
  </custom-checkbox-field>

  <div class="row mt-2">
    <form-field class="col-md-4" for-id="number" :label="$t('issue.number')" :required="false">
      <input id="number" class="form-control" type="number"
             v-model="filter.number">
    </form-field>
    <form-field class="col-md-8" for-id="description" :label="$t('issue.description')" :required="false">
      <input id="description" class="form-control" type="text"
             v-model="filter.description">
    </form-field>
  </div>


  <toggle-card
      v-if="configuration.showState"
      :title="$t('_form.issue_filter.state')" :initial-activated="configurationTemplate.state"
      @active-toggled="configuration.state = $event">
    <state-filter :initial-state="filter.state" @input="filter.state = $event" />
  </toggle-card>

  <toggle-card
      class="mt-2"
      :title="$t('craftsman._plural')" :initial-activated="configurationTemplate.craftsmen"
      @active-toggled="configuration.craftsmen = $event">
    <craftsmen-filter :initial-selected-entities="filter.craftsmen" :entities="craftsmen"
                      @input="filter.craftsmen = $event" />
  </toggle-card>

  <toggle-card
      class="mt-2"
      :title="$t('map._plural')" :initial-activated="configurationTemplate.maps"
      @active-toggled="configuration.maps = $event">
    <map-filter :initial-selected-entities="filter.maps" :entities="maps" @input="filter.maps = $event" />
  </toggle-card>

  <toggle-card
      class="mt-2"
      :title="$t('issue.deadline')" :initial-activated="configurationTemplate.deadline"
      @active-toggled="configuration.deadline = $event">
    <time-filter
        :label="$t('issue.deadline')"
        :initial-before="filter['deadline[before]']" :initial-after="filter['deadline[after]']"
        @input-before="filter['deadline[before]'] = $event" @input-after="filter['deadline[after]'] = $event"
    />
  </toggle-card>

  <toggle-card
      class="mt-2"
      :title="$t('_form.issue_filter.time')" :initial-activated="configurationTemplate.time"
      @active-toggled="configuration.time = $event">

    <time-filter
        v-if="configuration.showState || template.state === 1"
        :label="$t('issue.state.created')" :help="$t('issue.state.created_help')" :allow-future="false"
        :initial-before="filter['createdAt[before]']" :initial-after="filter['createdAt[after]']"
        @input-before="filter['createdAt[before]'] = $event" @input-after="filter['createdAt[after]'] = $event"
    />

    <time-filter
        v-if="configuration.showState || template.state === 2"
        :label="$t('issue.state.registered')" :help="$t('issue.state.registered_help')"
        :initial-before="filter['registeredAt[before]']" :initial-after="filter['registeredAt[after]']" :allow-future="false"
        @input-before="filter['registeredAt[before]'] = $event" @input-after="filter['registeredAt[after]'] = $event"
    />

    <time-filter
        v-if="configuration.showState || template.state === 4"
        :label="$t('issue.state.resolved')" :help="$t('issue.state.resolved_help')"
        :initial-before="filter['resolvedAt[before]']" :initial-after="filter['resolvedAt[after]']" :allow-future="false"
        @input-before="filter['resolvedAt[before]'] = $event" @input-after="filter['resolvedAt[after]'] = $event"
    />

    <time-filter
        v-if="configuration.showState || template.state === 8"
        :label="$t('issue.state.closed')" :help="$t('issue.state.closed_help')"
        :initial-before="filter['closedAt[before]']" :initial-after="filter['closedAt[after]']" :allow-future="false"
        @input-before="filter['closedAt[before]'] = $event" @input-after="filter['closedAt[after]'] = $event"
    />
  </toggle-card>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import StateFilter from './IssueFilter/StateFilter'
import ToggleCard from '../Library/Behaviour/ToggleCard'
import CraftsmenFilter from './IssueFilter/CraftsmenFilter'
import MapFilter from './IssueFilter/MapFilter'
import TimeFilter from './IssueFilter/TimeFilter'

export default {
  components: {
    TimeFilter,
    MapFilter,
    CraftsmenFilter,
    ToggleCard,
    StateFilter,
    CustomCheckboxField,
    FormField
  },
  emits: ['update', 'update-configuration'],
  data () {
    return {
      filter: {
        number: '',
        description: '',

        isMarked: null,
        wasAddedWithClient: null,

        state: null,
        craftsmen: null,
        maps: null,

        'deadline[before]': null,
        'deadline[after]': null,

        'createdAt[before]': null,
        'createdAt[after]': null,
        'registeredAt[before]': null,
        'registeredAt[after]': null,
        'resolvedAt[before]': null,
        'resolvedAt[after]': null,
        'closedAt[before]': null,
        'closedAt[after]': null
      },
      configuration: {
        state: false,
        craftsmen: false,
        maps: false,
        deadline: false,
        time: false
      },
    }
  },
  props: {
    template: {
      type: Object,
      required: true
    },
    configurationTemplate: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      default: []
    },
    craftsmen: {
      type: Array,
      default: []
    }
  },
  watch: {
    filter: {
      deep: true,
      handler: function () {
        this.$emit('update', this.filter)
      }
    },
    configuration: {
      deep: true,
      handler: function () {
        this.$emit('update-configuration', this.configuration)
      }
    }
  },
  mounted () {
    this.filter = Object.assign({}, this.filter, this.template)
    this.configuration = Object.assign({}, this.configuration, this.configurationTemplate)
  }
}
</script>
