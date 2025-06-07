<template>
  <div>
    <form-field :label="label" :required="false">
      <select class="form-select" v-model="value">
        <option :value="null">&mdash;</option>
        <option disabled></option>
        <option v-for="constructionManager in constructionManagers" :key="constructionManager['@id']"
                :value="constructionManager['@id']">
          {{ constructionManagerFormatter.name(constructionManager) }}
        </option>
      </select>
    </form-field>
  </div>
</template>

<script>
import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
import CustomCheckbox from '../../Library/FormInput/CustomCheckbox'
import {mapTransformer} from '../../../services/transformers'
import {entityFilterMixin} from './mixins'
import FormField from "../../Library/FormLayout/FormField.vue";
import {constructionManagerFormatter} from "../../../services/formatters";

export default {
  components: {
    FormField,
    CustomCheckbox,
    CustomCheckboxField
  },
  props: {
    label: {
      type: String,
      required: true
    },
    initial: {
      type: String,
      default: null
    },
    constructionManagers: {
      type: Array,
      default: []
    }
  },
  data() {
    return {
      value: null,
    }
  },
  watch: {
    value: function () {
      this.$emit('input', this.value)
    },
  },
  computed: {
    constructionManagerFormatter() {
      return constructionManagerFormatter
    }
  },
  mounted() {
    this.value = this.initial
  }
}
</script>

<style scoped lang="scss">
@for $i from 1 through 10 {
  .spacer-#{$i} {
    padding-left: $i*1em;
  }
}

</style>
