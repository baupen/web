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
            <a class="btn-link clickable" v-if="fields.isMarked.dirty" @click="reset('isMarked')">
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
            <a class="btn-link clickable" v-if="fields.wasAddedWithClient.dirty" @click="reset('wasAddedWithClient')">
              {{ $t('_form.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>
    </div>
    <div class="col-md-6 border-left" v-if="enableStateEdit">
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
            <a class="btn-link clickable" v-if="fields.isResolved.dirty" @click="reset('isResolved')">
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
            <a class="btn-link clickable" v-if="fields.isClosed.dirty" @click="reset('isClosed')">
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

  <hr />

  <form-field for-id="description" :label="$t('issue.description')">
    <input id="description" class="form-control" type="text" required="required" ref="description"
           :class="{'is-valid': fields.description.dirty && !fields.description.errors.length, 'is-invalid': fields.description.dirty && fields.description.errors.length }"
           v-model="issue.description"
           @change="validate('description')"
           @blur="fields.description.dirty = true"
           @keyup.enter="$emit('confirm')">
    <invalid-feedback :errors="fields.description.errors" />
    <a class="btn-link clickable" v-if="fields.description.dirty" @click="reset('description')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>

  <form-field for-id="craftsman" :label="$t('issue.craftsman')">
    <select class="custom-select"
            v-model="tradeFilter">
      <option v-for="trade in sortedTrade" :value="trade">
        {{ trade }}
      </option>
    </select>
    <select class="custom-select"
            :class="{'is-valid': fields.craftsman.dirty && !fields.craftsman.errors.length, 'is-invalid': fields.craftsman.dirty && fields.craftsman.errors.length }"
            v-model="issue.craftsman"
            @blur="fields.craftsman.dirty = true"
            @change="validate('craftsman')"
    >
      <option v-for="craftsman in sortedCraftsmen" :value="craftsman['@id']"
              :key="craftsman['@id']">
        {{ craftsman.company }} - {{ craftsman.contactName }}
      </option>
    </select>
    <invalid-feedback :errors="fields.description.errors" />
    <a class="btn-link clickable" v-if="fields.craftsman.dirty" @click="reset('craftsman')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>

  <form-field for-id="deadline" :label="$t('issue.deadline')" :required="false">
    <span ref="deadline-anchor" />
    <flat-pickr
        id="deadline" class="form-control"
        :class="{'is-valid': fields.deadline.dirty && !fields.deadline.errors.length, 'is-invalid': fields.deadline.dirty && fields.deadline.errors.length }"
        v-model="issue.deadline"
        :config="datePickerConfig"
        @blur="fields.deadline.dirty = true"
        @change="validate('deadline')">
    </flat-pickr>
    <invalid-feedback :errors="fields.deadline.errors" />
    <a class="btn-link clickable" v-if="fields.deadline.dirty" @click="reset('deadline')">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import { createField, validateField, changedFieldValues, resetFields } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import { dateConfig, flatPickr } from '../../services/flatpickr'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'

export default {
  components: {
    CustomCheckboxField,
    InvalidFeedback,
    FormField,
    flatPickr
  },
  emits: ['update', 'confirm'],
  data () {
    return {
      fields: {
        isMarked: createField(),
        wasAddedWithClient: createField(),
        description: createField(),
        craftsman: createField(),
        deadline: createField(),
        isResolved: createField(),
        isClosed: createField(),
      },
      issue: {
        isMarked: null,
        wasAddedWithClient: null,
        description: null,
        craftsman: null,
        deadline: null,
        isResolved: null,
        isClosed: null,
      },
      tradeFilter: null
    }
  },
  props: {
    template: {
      type: Object
    },
    craftsmen: {
      type: Array,
      required: true
    },
    enableStateEdit: {
      type: Boolean,
      default: false
    }
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
    sortedCraftsmen: function () {
      if (this.sortedCraftsmen.length === 1) {
        this.issue.craftsman = this.sortedCraftsmen[0]['@id']
        this.fields.craftsman.dirty = true
      }
    },
    'fields.deadline.dirty': function () {
      if (!this.$refs['deadline-anchor']) {
        return
      }

      const visibleInput = this.$refs['deadline-anchor'].parentElement.childNodes[4]
      if (this.fields.deadline.dirty) {
        visibleInput.classList.add('is-valid')
      } else {
        visibleInput.classList.remove('is-valid')
      }
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
      validateField(this.fields[field], this.issue[field])
    },
    reset: function (field) {
      if (field === 'craftsman') {
        this.tradeFilter = null
      }
      this.issue[field] = this.template[field]
      this.fields[field].dirty = false

      // need to wait for next tick for datetime picker
      this.$nextTick(() => {
        this.fields[field].dirty = false
      })
    },
    setIssueFromTemplate: function () {
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
    updatePayload: function () {
      if (this.fields.isMarked.errors.length ||
          this.fields.wasAddedWithClient.errors.length ||
          this.fields.description.errors.length ||
          this.fields.craftsman.errors.length ||
          this.fields.deadline.errors.length ||
          this.fields.isResolved.errors.length ||
          this.fields.isClosed.errors.length) {
        return null
      }

      const values = changedFieldValues(this.fields, this.issue, this.template)

      // ensure empty string is null
      if (Object.prototype.hasOwnProperty.call(values, 'deadline')) {
        values.deadline = values.deadline ? values.deadline : null

        // '2020-01-01'.length === 4+3+3
        if (values.deadline && this.template.deadline && values.deadline.substr(0, 10) === this.template.deadline.substr(0, 10)) {
          delete values.deadline
        }
      }

      return values
    },
  },
  mounted () {
    this.setIssueFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
