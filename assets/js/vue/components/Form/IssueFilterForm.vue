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

  <hr/>

  <toggle-card :title="$t('form.issue_filter.state')" v-if="showState" @active-toggled="filterActive.state = $event">
    <state-filter @input="filter.state = $event" />
  </toggle-card>

  <toggle-card class="mt-2" :title="$t('craftsman._plural')" v-if="showState" @active-toggled="filterActive.craftsmen = $event">
    <craftsmen-filter :craftsmen="craftsmen" @input="filter.craftsmen = $event" />
  </toggle-card>


  <!--

  <th>
    {{ $t('craftsman._name') }}

    <filter-popover
        :title="$t('issue_table.filter.by_craftsman')"
        :valid="filter.craftsmen.length < craftsmen.length && filter.craftsmen.length > 0">

      <craftsmen-filter class="mt-2" :craftsmen="craftsmen" @input="filter.craftsmen = $event" />
    </filter-popover>
  </th>
  <th>
    {{ $t('map._name') }}

    <filter-popover
        :title="$t('issue_table.filter.by_maps')"
        :valid="filter.maps.length < maps.length && filter.maps.length > 0">

      <map-filter class="mt-2" :maps="maps" @input="filter.maps = $event" />
    </filter-popover>
  </th>
  <th>
    {{ $t('issue.deadline') }}

    <filter-popover
        size="filter-wide"
        :title="$t('issue_table.filter.by_deadline')"
        :valid="!!(filter['deadline[before]'] && filter['deadline[after]'])">

      <deadline-filter
          @input-deadline-before="filter['deadline[before]'] = $event"
          @input-deadline-after="filter['deadline[after]'] = $event"
      />
    </filter-popover>
  </th>
  <th class="w-minimal">
    {{ $t('issue.status') }}


    <filter-popover
        size="filter-wide"
        :title="$t('issue_table.filter.by_state')"
        :valid="!forceState && filter.state !== 7">
      <template v-if="!forceState">
        <p class="font-weight-bold">{{ $t('issue_table.filter_state.by_active_state') }}</p>

        <state-filter
            v-if="showStateFilter"
            @input="filter.state = $event" />

        <hr />
      </template>

      <p class="font-weight-bold">{{ $t('issue_table.filter_time.by_time') }}</p>
      <time-filter
          :minimal-state="minimalState" :force-state="forceState"
          @input-registered-at-before="filter['registeredAt[before]'] = $event"
          @input-registered-at-after="filter['registeredAt[after]'] = $event"
          @input-resolved-at-before="filter['resolvedAt[before]'] = $event"
          @input-resolved-at-after="filter['resolvedAt[after]'] = $event"
          @input-closed-at-before="filter['closedAt[before]'] = $event"
          @input-closed-at-after="filter['closedAt[after]'] = $event"
      />
    </filter-popover>
  </th>
  -->
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import BooleanFilter from './IssueFilter/BooleanFilter'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import StateFilter from './IssueFilter/StateFilter'
import ToggleCard from '../Library/Behaviour/ToggleCard'
import CraftsmenFilter from './IssueFilter/CraftsmenFilter'

export default {
  components: {
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
        number: null,

        isMarked: null,
        wasAddedWithClient: null,

        description: '',
        craftsmen: [],
        maps: [],
        'deadline[before]': null,
        'deadline[after]': null,

        state: null,

        'createdAt[before]': null,
        'createdAt[after]': null,
        'registeredAt[before]': null,
        'registeredAt[after]': null,
        'resolvedAt[before]': null,
        'resolvedAt[after]': null,
        'closedAt[before]': null,
        'closedAt[after]': null,

        isDeleted: false
      },
      filterActive: {
        state: false,
        craftsmen: false,
        maps: false,
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
    actualFilter: function() {
      let actualFilter = Object.assign({}, this.filter)
      for (const field in this.filterActive) {
        if (Object.prototype.hasOwnProperty.call(this.filterActive, field)) {
          // set deactivated fields to null
          if (!this.filterActive[field]) {
            actualFilter[field] = null
          }
        }
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
