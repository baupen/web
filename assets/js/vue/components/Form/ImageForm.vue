<template>
  <form-field for-id="image" :label="$t('_form.image.label')">
    <dropzone
        v-if="!image"
        id="image" :help="$t('_form.image.drop_or_choose')"
        :valid-file-types="validFileTypes"
        @input="image = $event[0]" />
    <input v-if="image" id="image" class="form-control" type="text" readonly="readonly"
           :class="{'is-valid': imageIsValid, 'is-invalid': !imageIsValid && image !== null }"
           :value="image.name">
    <a class="btn-link clickable" v-if="image" @click="image = null">
      {{ $t('_form.image.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import { validImageTypes } from '../../services/api'

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      mounted: false,
      image: null
    }
  },
  watch: {
    image: function () {
      if (this.mounted) {
        this.$emit('update', this.image)
      }
    },
  },
  computed: {
    imageIsValid: function () {
      if (!this.image) {
        return false
      }

      return this.validFileTypes.some(e => this.image.type === e)
    },
    validFileTypes: function () {
      return validImageTypes;
    }
  },
  mounted () {
    this.mounted = true
    this.$emit('update', this.image)
  }
}
</script>
