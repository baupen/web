<template>
  <form-field for-id="name" :label="$t('construction_site.name')">
    <input id="name" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.name.dirty && !fields.name.errors.length, 'is-invalid': fields.name.dirty && fields.name.errors.length }"
           @blur="fields.name.dirty = true"
           v-model="constructionSite.name"
           @input="validate('name')">
    <invalid-feedback :errors="fields.name.errors" />
  </form-field>
</template>

<script>
import { changedFieldValues, createField, requiredRule, validateField, validateFields } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'

export default {
  components: {
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        name: createField(requiredRule())
      },
      constructionSite: {
        name: null
      }
    }
  },
  props: {
    template: {
      type: Object,
      required: true
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
      this.setConstructionSiteFromTemplate()
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.constructionSite[field])
    },
    setConstructionSiteFromTemplate: function () {
      if (this.template) {
        this.constructionSite = Object.assign({}, this.template)
      }

      validateFields(this.fields, this.constructionSite)
    }
  },
  computed: {
    updatePayload: function () {
      if (this.fields.name.errors.length) {
        return null
      }

      return changedFieldValues(this.fields, this.constructionSite, this.template)
    }
  },
  mounted () {
    this.setConstructionSiteFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
