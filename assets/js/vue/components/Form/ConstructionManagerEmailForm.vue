<template>
  <form-field for-id="email" :label="$t('construction_manager.email')">
    <input id="email" class="form-control" type="email" required="required"
           :class="{'is-valid': fields.email.dirty && !fields.email.errors.length, 'is-invalid': fields.email.dirty && fields.email.errors.length }"
           @blur="fields.email.dirty = true"
           v-model="constructionManager.email"
           @input="validate('email')">
    <invalid-feedback :errors="fields.email.errors" />
  </form-field>
</template>

<script>

import { createField, requiredRule, validateField, validateFields, changedFieldValues } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import Help from '../Library/FormLayout/Help'

export default {
  components: {
    Help,
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        email: createField(requiredRule()),
      },
      constructionManager: {
        email: null,
      },
    }
  },
  watch: {
    updatePayload: {
      deep: true,
      handler: function () {
        this.$emit('update', this.updatePayload)
      }
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.constructionManager[field])
    }
  },
  computed: {
    updatePayload: function () {
      if (this.fields.email.errors.length) {
        return null
      }

      return changedFieldValues(this.fields, this.constructionManager)
    }
  },
  mounted () {
    validateFields(this.fields, this.constructionManager)

    this.$emit('update', this.updatePayload)
  }
}
</script>
