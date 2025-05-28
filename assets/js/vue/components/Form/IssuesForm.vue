<template>
  <div class="row">
    <div class="col-md-6">
      <custom-checkbox-field for-id="is-marked" :label="$t('issue.is_marked')"
                             :label-icon="['fal', 'star']" :label-icon-checked="['fas', 'star']"
                             :checked="issue.isMarked">
        <input
            class="form-check-input" type="checkbox" id="is-marked"
            :class="{'is-valid': fields.isMarked.dirty && !fields.isMarked.errors.length, 'is-invalid': fields.isMarked.dirty && fields.isMarked.errors.length }"
            v-model="issue.isMarked"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.isMarked === null"
            @input="fields.isMarked.dirty = true"
            @change="validate('isMarked')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.isMarked.dirty && mode !== 'create'" @click="reset('isMarked')">
              {{ $t('_form.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>

      <custom-checkbox-field for-id="was-added-with-client" :label="$t('issue.was_added_with_client')"
                             :label-icon="['fal', 'user-check']" :label-icon-checked="['fas', 'user-check']"
                             :checked="issue.wasAddedWithClient">
        <input
            class="form-check-input" type="checkbox" id="was-added-with-client"
            :class="{'is-valid': fields.wasAddedWithClient.dirty && !fields.wasAddedWithClient.errors.length, 'is-invalid': fields.wasAddedWithClient.dirty && fields.wasAddedWithClient.errors.length }"
            v-model="issue.wasAddedWithClient"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.wasAddedWithClient === null"
            @input="fields.wasAddedWithClient.dirty = true"
            @change="validate('wasAddedWithClient')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.wasAddedWithClient.dirty && mode !== 'create'"
               @click="reset('wasAddedWithClient')">
              {{ $t('_form.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>
    </div>
    <div class="col-md-6 border-start" v-if="enableStateEdit">
      <custom-checkbox-field for-id="is-resolved" :label="$t('issue.state.resolved')">
        <input
            class="form-check-input" type="checkbox" id="is-resolved"
            :class="{'is-valid': fields.isResolved.dirty && !fields.isResolved.errors.length, 'is-invalid': fields.isResolved.dirty && fields.isResolved.errors.length }"
            v-model="issue.isResolved"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.isResolved === null"
            @input="fields.isResolved.dirty = true"
            @change="validate('isResolved')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.isResolved.dirty && mode !== 'create'"
               @click="reset('isResolved')">
              {{ $t('_form.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>

      <custom-checkbox-field for-id="is-closed" :label="$t('issue.state.closed')">
        <input
            class="form-check-input" type="checkbox" id="is-closed"
            :class="{'is-valid': fields.isClosed.dirty && !fields.isClosed.errors.length, 'is-invalid': fields.isClosed.dirty && fields.isClosed.errors.length }"
            v-model="issue.isClosed"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.isClosed === null"
            @input="fields.isClosed.dirty = true"
            @change="validate('isClosed')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.isClosed.dirty && mode !== 'create'" @click="reset('isClosed')">
              {{ $t('_form.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>
    </div>
  </div>

  <p class="alert alert-warning mt-2" v-if="fields.isResolved.dirty && issue.isResolved">
    {{ $t('_form.issues.resolved_as_impersonated_craftsman') }}
  </p>

  <hr/>

  <template v-if="mode === 'create' || mode === 'edit_single'">
    <form-field for-id="map" :label="$t('issue.map')">
      <select class="form-select mb-2"
              :class="{'is-valid': fields.map.dirty && !fields.map.errors.length, 'is-invalid': fields.map.dirty && fields.map.errors.length }"
              v-model="issue.map"
              @change="validate('map')"
      >
        <option v-for="mapContainer in mapContainers" :value="mapContainer.entity['@id']"
                :key="mapContainer.entity['@id']">
          {{ '&nbsp;'.repeat(mapContainer.level*2) }}{{ mapContainer.entity.name }}
        </option>
      </select>
      <invalid-feedback :errors="fields.map.errors"/>

      <template v-if="selectedMap && selectedMap.fileUrl">
        <div class="d-flex justify-content-between">
          <div class="me-3">
            <div>
              <set-map-position-button
                  v-if="position === undefined"
                  :construction-site="constructionSite" :map="selectedMap"
                  :current-position="currentPosition"
                  @selected="position = $event"/>
              <template v-else-if="position === null">
                <input id="position" class="form-control is-valid" type="text" readonly
                       :value="$t('_form.issues.position_null')">
              </template>
              <template v-else>
                <input id="position" class="form-control is-valid" type="text" readonly
                       :value="$t('_form.issues.position_set')">
              </template>
            </div>

            <p class="mb-0" v-if="this.position !== undefined && (mode === 'edit_single' || mode === 'create')">
              <a class="btn-link clickable"
                 @click="position = undefined">
                {{ $t('_form.reset') }}
              </a>
            </p>

            <p class="mb-0" v-else-if="fields.map.dirty && mode === 'edit_single'">
              <a class="btn-link clickable"
                 @click="reset('map')">
                {{ $t('_form.reset') }}
              </a>
            </p>
          </div>
          <div>
            <map-position-canvas
                :construction-site="constructionSite" :map="selectedMap" :position="position ?? currentPosition"
                :inline="true"/>
          </div>
        </div>
      </template>
    </form-field>

    <hr/>

  </template>

  <slot name="before-description"/>
  <form-field for-id="description" :label="$t('issue.description')" :required="false">
    <input id="description" class="form-control" type="text" ref="description"
           :class="{'is-valid': fields.description.dirty && !fields.description.errors.length, 'is-invalid': fields.description.dirty && fields.description.errors.length }"
           v-model="issue.description"
           @change="validate('description')"
           @blur="fields.description.dirty = true"
           @keyup.enter="$emit('confirm')">
    <invalid-feedback :errors="fields.description.errors"/>
    <a class="btn-link clickable" v-if="fields.description.dirty && mode !== 'create'" @click="reset('description')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>

  <hr/>

  <form-field for-id="craftsman" :label="$t('issue.craftsman')" :required="false">
    <select class="form-select mb-1"
            v-model="tradeFilter">
      <option v-for="trade in sortedTrade" :value="trade">
        {{ trade }}
      </option>
    </select>
    <select class="form-select"
            :class="{'is-valid': fields.craftsman.dirty && !fields.craftsman.errors.length, 'is-invalid': fields.craftsman.dirty && fields.craftsman.errors.length }"
            v-model="issue.craftsman"
            @change="validate('craftsman')"
    >
      <option v-for="craftsman in sortedCraftsmen" :value="craftsman['@id']"
              :key="craftsman['@id']">
        {{ craftsman.company }} - {{ craftsman.contactName }}
      </option>
    </select>
    <invalid-feedback :errors="fields.description.errors"/>
    <a class="btn-link clickable" v-if="fields.craftsman.dirty && mode !== 'create'" @click="reset('craftsman')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>

  <form-field for-id="deadline" :label="$t('issue.deadline')" :required="false">
    <span ref="deadline-anchor"/>
    <flat-pickr
        id="deadline" class="form-control"
        v-model="issue.deadline"
        :config="datePickerConfig"
        @blur="fields.deadline.dirty = true"
        @change="validate('deadline')">
    </flat-pickr>
    <invalid-feedback :errors="fields.deadline.errors"/>
    <a class="btn-link clickable" v-if="fields.deadline.dirty && mode !== 'create'" @click="reset('deadline')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import {createField, validateField, changedFieldValues, resetFields, requiredRule} from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import {dateConfig, flatPickr, toggleAnchorValidity} from '../../services/flatpickr'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import {mapTransformer} from "../../services/transformers";
import SetMapPositionButton from "../Action/SetMapPositionButton.vue";
import MapPositionCanvas from "../View/MapPositionCanvas.vue";

export default {
  components: {
    MapPositionCanvas,
    SetMapPositionButton,
    CustomCheckboxField,
    InvalidFeedback,
    FormField,
    flatPickr
  },
  emits: ['update', 'confirm'],
  data() {
    return {
      fields: {
        isMarked: createField(),
        wasAddedWithClient: createField(),
        description: createField(),
        craftsman: createField(),
        map: createField(),
        deadline: createField(),
        isResolved: createField(),
        isClosed: createField(),
      },
      issue: {
        isMarked: null,
        wasAddedWithClient: null,
        description: null,
        craftsman: null,
        map: null,
        deadline: null,
        isResolved: null,
        isClosed: null,
      },
      position: undefined,
      tradeFilter: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    template: {
      type: Object,
      required: false
    },
    mode: {
      type: String,
      required: true
    },
    enableStateEdit: {
      type: Boolean,
      default: false
    },
  },
  watch: {
    updatePayload: {
      deep: true,
      handler: function () {
        this.$emit('update', this.updatePayload)
      }
    },
    template: function () {
      this.setIssueFromTemplate()
    },
    selectedMap: function () {
      this.position = undefined
    },
    sortedCraftsmen: function () {
      if (this.sortedCraftsmen.length === 1) {
        this.issue.craftsman = this.sortedCraftsmen[0]['@id']
        this.fields.craftsman.dirty = true
      }
    },
    'fields.deadline.dirty': function () {
      toggleAnchorValidity(this.$refs['deadline-anchor'], this.fields.deadline)
    },
    'fields.deadline.errors.length': function () {
      toggleAnchorValidity(this.$refs['deadline-anchor'], this.fields.deadline)
    }
  },
  methods: {
    selectDescription: function () {
      // called from parent
      this.$nextTick(() => {
        this.$refs['description'].focus()
      })
    },
    validate: function (field) {
      if (field === 'map' || field === 'craftsman') {
        this.fields[field].dirty = true
      }
      if (field === 'craftsman') {
        this.tradeFilter = this.craftsmen.find(c => c['@id'] === this.issue[field]).trade
      }
      validateField(this.fields[field], this.issue[field])
    },
    reset: function (field) {
      if (field === 'craftsman') {
        this.tradeFilter = this.craftsmen.find(c => c['@id'] === this.template.craftsman).trade
      }
      if (field === 'map') {
        this.position = undefined
      }
      this.issue[field] = this.template[field]
      this.fields[field].dirty = false

      // need to wait for next tick for datetime picker
      this.$nextTick(() => {
        this.fields[field].dirty = false
      })
    },
    setIssueFromTemplate: function () {
      if (!this.template) {
        return
      }

      this.issue = Object.assign({}, this.template)
      if (this.issue.craftsman) {
        this.tradeFilter = this.craftsmen.find(c => c['@id'] === this.issue.craftsman).trade
      } else {
        this.tradeFilter = null
      }

      this.$nextTick(() => {
        resetFields(this.fields)
      })
    },
  },
  computed: {
    datePickerConfig: function () {
      return dateConfig
    },
    mapContainers: function () {
      return mapTransformer.orderedList(this.maps.filter(m => !m.isDeleted), mapTransformer.PROPERTY_LEVEL)
    },
    selectableCraftsmen: function () {
      return this.craftsmen.filter(c => !c.isDeleted)
    },
    sortedCraftsmen: function () {
      let selectableCraftsmen = this.selectableCraftsmen
      if (this.tradeFilter) {
        selectableCraftsmen = selectableCraftsmen.filter(c => c.trade === this.tradeFilter)
      }

      return selectableCraftsmen.sort((a, b) => a.company.localeCompare(b.company))
    },
    sortedTrade: function () {
      const tradeSet = new Set(this.selectableCraftsmen.map(c => c.trade))

      return Array.from(tradeSet).sort()
    },
    currentPosition: function () {
      // ensure it shows up when selecting a new position
      if (this.template.positionX === null || this.template.positionY === null || this.template.positionZoomScale === null) {
        return null
      }

      return {x: this.template.positionX, y:this.template.positionY, zoomScale: this.template.positionZoomScale}
    },
    updatePayload: function () {
      if (this.fields.isMarked.errors.length ||
          this.fields.wasAddedWithClient.errors.length ||
          this.fields.description.errors.length ||
          this.fields.craftsman.errors.length ||
          this.fields.map.errors.length ||
          this.fields.deadline.errors.length ||
          this.fields.isResolved.errors.length ||
          this.fields.isClosed.errors.length) {
        return null
      }

      const values = changedFieldValues(this.fields, this.issue, this.template)

      if (this.position !== undefined) {
        if (this.position !== null) {
          values['positionX'] = this.position.x
          values['positionY'] = this.position.y
          values['positionZoomScale'] = this.position.zoomScale
        } else {
          values['positionX'] = null
          values['positionY'] = null
          values['positionZoomScale'] = null
        }
      }

      // ensure empty string is null
      if (Object.prototype.hasOwnProperty.call(values, 'deadline')) {
        values.deadline = values.deadline ? values.deadline : null

        // '2020-01-01'.length === 4+3+3
        if (values.deadline && this.template?.deadline && values.deadline.substr(0, 10) === this.template?.deadline.substr(0, 10)) {
          delete values.deadline
        }
      }

      return values
    },
    selectedMap: function () {
      if (!this.issue.map) {
        if (!this.template?.map) {
          return null
        }

        return this.maps.find(map => map['@id'] === this.template?.map)
      }

      return this.maps.find(map => map['@id'] === this.issue.map)
    }
  },
  mounted() {
    this.setIssueFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>

