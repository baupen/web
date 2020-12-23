<template>
  <div>
    <text-edit id="name" :label="$t('construction_site.name')"
               v-model="modelValue.name"
               @input="emitUpdate"
               @valid="validProperties.name = $event"
               :focus="true" />

    <text-edit id="streetAddress" :label="$t('construction_site.street_address')"
               v-model="modelValue.streetAddress"
               @input="emitUpdate"
               @valid="validProperties.streetAddress = $event"/>

    <div class="form-row">
      <text-edit id="postalCode" :label="$t('construction_site.postal_code')" :size="4" type="number"
                 v-model.number="modelValue.postalCode"
                 @input="emitUpdate"
                 @valid="validProperties.postalCode = $event"/>

      <text-edit id="locality" :label="$t('construction_site.locality')" :size="8"
                 v-model="modelValue.locality"
                 @input="emitUpdate"
                 @valid="validProperties.locality = $event"/>
    </div>
  </div>
</template>

<script>
import TextEdit from "./TextEdit";

export default {
  components: {TextEdit},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      validProperties: {
        template: false,
        subject: false,
        body: false,
      }
    }
  },
  props: {
    modelValue: {
      type: Object
    },
    constructionSites: {
      type: Array,
      required: true
    }
  },
  watch: {
    isValid: function () {
      this.emitValid();
    }
  },
  methods: {
    emitUpdate: function () {
      this.$emit('update:modelValue', {...this.modelValue})
    },
    emitValid: function () {
      this.$emit('valid', this.isValid)
    }
  },
  computed: {
    isValid: function () {
      return this.validProperties.name &&
          this.validProperties.streetAddress &&
          this.validProperties.postalCode &&
          this.validProperties.locality;
    }
  },
  mounted() {
    this.emitValid();
  }
}
</script>
