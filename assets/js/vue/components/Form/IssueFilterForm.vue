<template>
  <div class="form-row">
    <form-field class="col-md-4" for-id="number" :label="$t('issue.number')">
      <input id="number" class="form-control" type="number"
             v-model="filter.number">
    </form-field>
    <form-field class="col-md-8" for-id="description" :label="$t('issue.description')">
      <input id="description" class="form-control" type="text"
             v-model="filter.description">
    </form-field>
  </div>

  <custom-checkbox-field
      for-id="filter-is-marked" :label="$t('issue.is_marked')"
      :show-reset="filter.isMarked !== null" @reset="filter.isMarked = null">
    <input
        class="custom-control-input" type="checkbox" id="filter-is-marked"
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
        class="custom-control-input" type="checkbox" id="filter-was-added-with-client"
        v-model="filter.wasAddedWithClient"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="filter.wasAddedWithClient === null"
    >
  </custom-checkbox-field>

  <hr />

  <toggle-card :title="$t('form.issue_filter.state')" v-if="showState" @active-toggled="filterActive.state = $event">
    <state-filter @input="filter.state = $event" />
  </toggle-card>

  <toggle-card class="mt-2" :title="$t('craftsman._plural')"
               @active-toggled="filterActive.craftsmen = $event">
    <craftsmen-filter :craftsmen="craftsmen" @input="filter.craftsmen = $event" />
  </toggle-card>

  <toggle-card class="mt-2" :title="$t('map._plural')" @active-toggled="filterActive.maps = $event">
    <map-filter :maps="maps" @input="filter.maps = $event" />
  </toggle-card>

  <toggle-card class="mt-2" :title="$t('issue.deadline')"
               @active-toggled="filterActive.deadline = $event">
    <time-filter
        :label="$t('issue.deadline')"
        @input-before="filter['deadline[before]'] = $event"
        @input-after="filter['deadline[after]'] = $event"
    />
  </toggle-card>

  <toggle-card class="mt-2" :title="$t('form.issue_filter.time')"
               @active-toggled="filterActive.time = $event">
    
    <time-filter
        v-if="showState || template.state === 1"
        :label="$t('issue.state.created')"
        :help="$t('issue.state.created_help')"
        @input-before="filter['createdAt[before]'] = $event"
        @input-after="filter['createdAt[after]'] = $event"
    />

    <time-filter
        v-if="showState || template.state === 2"
        :label="$t('issue.state.registered')"
        :help="$t('issue.state.registered_help')"
        @input-before="filter['registeredAt[before]'] = $event"
        @input-after="filter['registeredAt[after]'] = $event"
    />

    <time-filter
        v-if="showState || template.state === 4"
        :label="$t('issue.state.resolved')"
        :help="$t('issue.state.resolved_help')"
        @input-before="filter['resolvedAt[before]'] = $event"
        @input-after="filter['resolvedAt[after]'] = $event"
    />

    <time-filter
        v-if="showState || template.state === 8"
        :label="$t('issue.state.closed')"
        :help="$t('issue.state.closed_help')"
        @input-before="filter['closedAt[before]'] = $event"
        @input-after="filter['closedAt[after]'] = $event"
    />
  </toggle-card>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import BooleanFilter from './IssueFilter/BooleanFilter'
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
    BooleanFilter,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      mounted: false,
      filter: {
        number: '',
        description: '',

        isMarked: null,
        wasAddedWithClient: null,

        state: null,
        craftsmen: [],
        maps: [],

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
      filterActive: {
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
      type: Object
    },
    showState: {
      type: Boolean,
      required: true
    },
    state: {
      type: Number,
      default: 0
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
    actualFilter: {
      deep: true,
      handler: function () {
        this.$emit('update', this.actualFilter)
      }
    },
    template: function () {
      this.setFilterFromTemplate()
    }
  },
  computed: {
    actualFilter: function () {
      let actualFilter = Object.assign({}, this.filter)
      if (!this.filterActive.state) {
        actualFilter.state = null
      }
      if (!this.filterActive.craftsmen) {
        actualFilter.craftsmen = null
      }
      if (!this.filterActive.maps) {
        actualFilter.maps = null
      }
      if (!this.filterActive.deadline) {
        actualFilter['deadline[before]'] = null
        actualFilter['deadline[after]'] = null
      }
      if (!this.filterActive.time) {
        actualFilter['createdAt[before]'] = null
        actualFilter['createdAt[after]'] = null
        actualFilter['registeredAt[before]'] = null
        actualFilter['registeredAt[after]'] = null
        actualFilter['resolvedAt[before]'] = null
        actualFilter['resolvedAt[after]'] = null
        actualFilter['closedAt[before]'] = null
        actualFilter['closedAt[after]'] = null
      }

      return actualFilter
    }
  },
  methods: {
    setFilterFromTemplate: function () {
      if (this.template) {
        this.filter = Object.assign({}, this.filter, this.template)
      }
    }
  },
  mounted () {
    this.setFilterFromTemplate()
  }
}
</script>
