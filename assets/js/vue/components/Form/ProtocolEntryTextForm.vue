<template>
  <form-field for-id="payload" :label="$t('protocol_entry.text_payload')" :required="false">
        <textarea id="payload" class="form-control"
                  :class="{'is-valid': fields.payload.dirty && !fields.payload.errors.length, 'is-invalid': fields.payload.dirty && fields.payload.errors.length }"
                  @blur="fields.payload.dirty = true"
                  v-model="protocolEntry.payload"
                  @input="validate('payload')"
                  rows="3">
        </textarea>
    <invalid-feedback :errors="fields.payload.errors"/>
  </form-field>

  <form-field for-id="createdAt" :label="$t('protocol_entry.created_at')" :required="false">
    <span ref="createdAt-anchor"/>
    <flat-pickr
        id="createdAt" class="form-control"
        v-model="protocolEntry.createdAt"
        :config="dateTimePickerConfig"
        @blur="fields.createdAt.dirty = true"
        @change="validate('createdAt')">
    </flat-pickr>
    <invalid-feedback :errors="fields.createdAt.errors"/>
  </form-field>
</template>

<script>

import {
  createField,
  requiredRule,
  validateField, validateFields,
} from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import Help from '../Library/FormLayout/Help'
import {dateTimeConfig, flatPickr, toggleAnchorValidity} from "../../services/flatpickr";

export default {
  components: {
    Help,
    InvalidFeedback,
    FormField,
    flatPickr
  },
  emits: ['update'],
  data() {
    return {
      fields: {
        payload: createField(requiredRule()),
        createdAt: createField(requiredRule()),
      },
      protocolEntry: {
        type: 'TEXT',
        payload: null,
        createdAt: (new Date()).toISOString()
      },
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
      this.setFromTemplate()
    },
    'fields.createdAt.dirty': function () {
      toggleAnchorValidity(this.$refs['createdAt-anchor'], this.fields.createdAt)
    },
    'fields.createdAt.errors.length': function () {
      toggleAnchorValidity(this.$refs['createdAt-anchor'], this.fields.createdAt)
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.protocolEntry[field])
    },
    setFromTemplate: function () {
      if (this.template) {
        this.protocolEntry = Object.assign({}, this.protocolEntry, this.template)
      }

      validateFields(this.fields, this.protocolEntry)
    }
  },
  computed: {
    dateTimePickerConfig: function () {
      return dateTimeConfig
    },
    updatePayload: function () {
      if (this.fields.payload.errors.length ||
          this.fields.createdAt.errors.length) {
        return null
      }

      return this.protocolEntry
    },
  },
  mounted() {
    this.setFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
