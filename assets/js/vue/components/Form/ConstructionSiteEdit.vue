<template>
  <div>
    <text-edit id="name" :label="$t('construction_site.name')"
               v-model="modelValue.name"
               @input="update('name', $event.target.value)"
               :focus="true" />

    <text-edit id="streetAddress" :label="$t('construction_site.street_address')"
               v-model="modelValue.streetAddress"
               @input="update('streetAddress', $event.target.value)"/>

    <div class="form-row">
      <text-edit id="postalCode" :label="$t('construction_site.postal_code')" :size="4" type="number"
                 v-model="modelValue.postalCode"
                 @input="update('postalCode', parseInt($event.target.value))"/>

      <text-edit id="locality" :label="$t('construction_site.locality')" :size="8"
                 v-model="modelValue.locality"
                 @input="update('locality', $event.target.value)"/>
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
        name: false,
        streetAddress: false,
        postalCode: false,
        locality: false,
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
  methods: {
    update: function (key, value) {
      this.$emit('update:modelValue', {...this.modelValue, [key]: value})
    }
  }
}
</script>
