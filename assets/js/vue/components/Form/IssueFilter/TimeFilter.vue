<template>
  <div class="row">
    <form-field :label="label">
      <div class="input-group">
        <flat-pickr
            class="form-control"
            :placeholder="$t('_form.issue_filter.from')"
            v-model="after"
            :config="dateTimePickerConfig">
        </flat-pickr>
        <div class="input-group-prepend">
          <div class="input-group-text">-</div>
        </div>
        <flat-pickr
            class="form-control"
            :placeholder="$t('_form.issue_filter.until')"
            v-model="before"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </div>
      <p v-if="help" class="text-muted mt-1 mb-0">
        <small>
          {{help}}
        </small>
      </p>
    </form-field>
  </div>
</template>


<script>

import { dateConfig, flatPickr } from '../../../services/flatpickr'
import FormField from '../../Library/FormLayout/FormField'

export default {
  components: {
    FormField,
    flatPickr
  },
  emits: [
    'input-before',
    'input-after',
  ],
  props: {
    label: {
      type: String,
      required: true
    },
    initialBefore: {
      type: String,
      required: false
    },
    initialAfter: {
      type: String,
      required: false
    },
    allowFuture: {
      type: Boolean,
      default: true
    },
    help: {
      type: String,
      required: false
    }
  },
  data () {
    return {
      before: null,
      after: null
    }
  },
  watch: {
    before: function () {
      this.$emit('input-before', this.normalize(this.before))
    },
    after: function () {
      this.$emit('input-after', this.normalize(this.after))
    }
  },
  methods: {
    normalize: function (value) {
      if (!value) {
        return null
      }

      return value
    }
  },
  computed: {
    dateTimePickerConfig: function () {
      if (this.allowFuture) {
        return dateConfig
      }
      return Object.assign({maxDate: new Date()}, dateConfig)
    }
  },
  mounted () {
    if (this.initialBefore) {
      this.before = this.initialBefore
    }
    if (this.initialAfter) {
      this.after = this.initialAfter
    }
  }
}
</script>
