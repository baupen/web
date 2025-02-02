<template>
  <form-field for-id="timestamp" :label="$t('issue_event.timestamp')" :required="true">
    <span ref="timestamp-anchor"/>
    <flat-pickr
        id="timestamp" class="form-control"
        v-model="meta.timestamp"
        :config="dateTimePickerConfig"
        @blur="fields.timestamp.dirty = true"
        @change="validate('timestamp')">
    </flat-pickr>
    <invalid-feedback :errors="fields.timestamp.errors"/>
  </form-field>

  <custom-checkbox-field
      for-id="contextual-for-children"
      v-if="showContextualForChildren"
      :label="contextualForChildrenLabel">
    <input
        class="form-check-input" type="checkbox" id="contextual-for-children"
        :class="{'is-valid': fields.contextualForChildren.dirty && !fields.contextualForChildren.errors.length }"
        v-model="meta.contextualForChildren"
        :true-value="true"
        :false-value="false"
        @input="fields.contextualForChildren.dirty = true"
        @change="validate('contextualForChildren')"
    >
    <template v-slot:after>
      <invalid-feedback :errors="fields.contextualForChildren.errors"/>
    </template>
  </custom-checkbox-field>
</template>

<script>

import {
  createField, fieldValues,
  requiredRule,
  validateField, validateFields,
} from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import {dateTimeConfig, flatPickr, toggleAnchorValidity} from "../../services/flatpickr";
import CustomCheckboxField from "../Library/FormLayout/CustomCheckboxField.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";

export default {
  components: {
    ButtonWithModalConfirm,
    CustomCheckboxField,
    InvalidFeedback,
    FormField,
    flatPickr
  },
  emits: ['update'],
  data() {
    return {
      fields: {
        timestamp: createField(requiredRule()),
        contextualForChildren: createField(),
      },
      meta: {
        timestamp: null,
        contextualForChildren: true,
      },
    }
  },
  props: {
    template: {
      type: Object
    },
    root: {
      type: Object
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
    'meta.timestamp': function () {
      validateField(this.fields['timestamp'], this.meta['timestamp'])
    },
    'fields.timestamp.dirty': function () {
      toggleAnchorValidity(this.$refs['timestamp-anchor'], this.fields.timestamp)
    },
    'fields.timestamp.errors.length': function () {
      toggleAnchorValidity(this.$refs['timestamp-anchor'], this.fields.timestamp)
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.meta[field])
    },
    setFromTemplate: function () {
      if (this.template) {
        this.meta = Object.assign({}, this.meta, this.template)
      }

      validateFields(this.fields, this.meta)
    }
  },
  computed: {
    showContextualForChildren: function () {
      return this.rootIsConstructionSite || this.rootIsCraftsman
    },
    contextualForChildrenLabel: function () {
      if (this.rootIsConstructionSite) {
        return this.$t('_action.add_issue_event.add_event_to_all_issues')
      } else {
        return this.$t('_action.add_issue_event.add_event_to_all_craftsman_issues')
      }
    },
    rootIsConstructionSite: function () {
      return this.root['@id'].includes('construction_sites')
    },
    rootIsCraftsman: function () {
      return this.root['@id'].includes('craftsmen')
    },
    dateTimePickerConfig: function () {
      return dateTimeConfig
    },
    updatePayload: function () {
      if (this.fields.timestamp.errors.length ||
          this.fields.contextualForChildren.errors.length) {
        return null
      }

      return fieldValues(this.fields, this.meta)
    },
  },
  mounted() {
    this.setFromTemplate()
  }
}
</script>
