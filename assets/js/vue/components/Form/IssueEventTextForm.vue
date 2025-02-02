<template>
  <form-field for-id="payload" :label="textMode ? $t('issue_event.text_payload') : $t('issue_event.file_payload')"
              :required="textMode">
        <textarea id="payload" class="form-control"
                  :class="{'is-valid': fields.payload.dirty && !fields.payload.errors.length, 'is-invalid': fields.payload.dirty && fields.payload.errors.length }"
                  @blur="fields.payload.dirty = true"
                  v-model="issueEvent.payload"
                  @input="validate('payload')"
                  :rows="textMode ? 3 : 1"
        >
        </textarea>
    <invalid-feedback :errors="fields.payload.errors"/>
  </form-field>
</template>

<script>

import {
  createField, fieldValues,
  requiredRule,
  validateField, validateFields,
} from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'

export default {
  components: {
    InvalidFeedback,
    FormField,
  },
  emits: ['update'],
  data() {
    return {
      fields: {
        payload: createField(),
      },
      issueEvent: {
        payload: null,
      },
    }
  },
  props: {
    template: {
      type: Object
    },
    textMode: {
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
      this.setFromTemplate()
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.issueEvent[field])
    },
    setFromTemplate: function () {
      if (this.template) {
        this.issueEvent = Object.assign({}, this.issueEvent, this.template)
      }

      validateFields(this.fields, this.issueEvent)
    }
  },
  computed: {
    updatePayload: function () {
      if (this.fields.payload.errors.length) {
        return null
      }

      return fieldValues(this.fields, this.issueEvent)
    },
  },
  mounted() {
    this.setFromTemplate()

    if (this.textMode) {
      this.fields.payload.rules.push(requiredRule())
      validateField(this.fields['payload'], this.issueEvent['payload'])
    }
  }
}
</script>
