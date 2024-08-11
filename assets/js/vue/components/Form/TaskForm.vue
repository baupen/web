<template>
  <form-field for-id="description" :label="$t('task.description')">
    <input id="description" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.description.dirty && !fields.description.errors.length, 'is-invalid': fields.description.dirty && fields.description.errors.length }"
           @blur="fields.description.dirty = true"
           v-model="task.description"
           @input="validate('description')">
    <invalid-feedback :errors="fields.description.errors" />
  </form-field>

  <form-field for-id="deadline" :label="$t('task.deadline')" :required="false">
    <span ref="deadline-anchor" />
    <flat-pickr
        id="deadline" class="form-control"
        v-model="task.deadline"
        :config="datePickerConfig"
        @blur="fields.deadline.dirty = true"
        @change="validate('deadline')">
    </flat-pickr>
    <invalid-feedback :errors="fields.deadline.errors" />
  </form-field>
</template>

<script>

import {
  createField,
  requiredRule,
  validateField,
  validateFields,
  changedFieldValues,
} from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import Help from '../Library/FormLayout/Help'
import {dateConfig, flatPickr, toggleAnchorValidity} from "../../services/flatpickr";

export default {
  components: {
    Help,
    InvalidFeedback,
    FormField,
    flatPickr
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        description: createField(requiredRule()),
        deadline: createField(),
      },
      task: {
        description: null,
        deadline: null,
      },
    }
  },
  props: {
    template: {
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
      this.setTaskFromTemplate()
    },
    'fields.deadline.dirty': function () {
      toggleAnchorValidity(this.$refs['deadline-anchor'], this.fields.deadline)
    },
    'fields.deadline.errors.length': function () {
      toggleAnchorValidity(this.$refs['deadline-anchor'], this.fields.deadline)
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.task[field])
    },
    setTaskFromTemplate: function () {
      if (this.template) {
        this.task = Object.assign({}, this.template)
      }

      validateFields(this.fields, this.task)
    }
  },
  computed: {
    datePickerConfig: function () {
      return dateConfig
    },
    updatePayload: function () {
      if (this.fields.description.errors.length ||
          this.fields.deadline.errors.length) {
        return null
      }

      return changedFieldValues(this.fields, this.task, this.templateTransformed)
    },
  },
  mounted () {
    this.setTaskFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
