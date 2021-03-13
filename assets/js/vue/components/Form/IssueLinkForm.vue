<template>
  <form-field for-id="link-access-allowed-before"
              :required="false"
              :label="$t('_form.issue_link.access_allowed_before')">
    <flat-pickr
        id="link-access-allowed-before" class="form-control"
        v-model="link.accessAllowedBefore"
        :config="datePickerConfig">
    </flat-pickr>
  </form-field>

</template>

<script>

import { dateConfig, flatPickr } from '../../services/flatpickr'
import FormField from '../Library/FormLayout/FormField'

export default {
  components: {
    FormField,
    flatPickr
  },
  emits: ['update'],
  data () {
    return {
      link: {
        accessAllowedBefore: null
      }
    }
  },
  props: {
    template: {
      type: Object
    }
  },
  computed: {
    datePickerConfig: function () {
      return dateConfig
    },
    actualLink: function () {
      return {
        accessAllowedBefore: this.normalizeDateTime(this.link.accessAllowedBefore)
      }
    }
  },
  watch: {
    actualLink: {
      deep: true,
      handler: function () {
        this.$emit('update', this.actualLink)
      }
    }
  },
  methods: {
    normalizeDateTime: function (value) {
        return value ? value : null
    }
  },
  mounted () {
    this.link = Object.assign({}, this.template)
    this.$emit('update', this.link)
  }
}
</script>
