<template>
  <div class="form-row">
    <form-field class="col-md-4" for-id="description" :label="$t('issue.description')">
      <input id="description" class="form-control" type="text"
             v-model="filter.description">
    </form-field>
    <form-field class="col-md-8" for-id="number" :label="$t('issue.number')">
      <input id="number" class="form-control" type="number"
             v-model="filter.number">
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


  <!--
  <search-popover
      :title="$t('issue_table.filter.by_number')" :valid="!!(filter.number || filter.number === 0)"
      @shown="$refs['filter-number'].focus()">
    <input class="form-control" ref="filter-number" v-model.number="filter.number" type="number"
           name="filter-number">
  </search-popover>

  <th class="w-minimal">
    <search-popover
        :title="$t('issue_table.filter.by_number')" :valid="!!(filter.number || filter.number === 0)"
        @shown="$refs['filter-number'].focus()">
      <input class="form-control" ref="filter-number" v-model.number="filter.number" type="number"
             name="filter-number">
    </search-popover>
  </th>
  <th class="w-thumbnail"></th>
  <th>
          <span class="mr-1">
            {{ $t('issue.description') }}
          </span>
    <search-popover
        :title="$t('issue_table.filter.by_description')" :valid="!!(filter.description)"
        @shown="$refs['filter-description'].focus()">
      <input class="form-control" ref="filter-description" v-model="filter.description" type="text"
             name="filter-description">
    </search-popover>
  </th>
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

export default {
  components: {
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
    filter: {
      deep: true,
      handler: function () {
        if (this.mounted) {
          this.$emit('update', this.filter)
        }
      }
    },
    template: function () {
      this.setFilterFromTemplate()
    }
  },
  computed: {
    showStateFilter: function () {
      return this.showStateFilter
    },

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

    this.mounted = true
    this.$emit('update', this.filter)
  }
}
</script>
