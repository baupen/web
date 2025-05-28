<template>
  <form-field for-id="image" :label="$t('_form.image.label')" :required="false">
    <dropzone
        v-if="!image"
        id="image" :help="dropHelp"
        :valid-file-types="validFileTypes"
        @input="image = $event[0]" />
    <input v-if="image" id="image" class="form-control" type="text" readonly="readonly"
           :class="{'is-valid': imageIsValid, 'is-invalid': !imageIsValid }"
           :value="image.name">
    <a class="btn-link clickable" v-if="image" @click="image = null">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import { validImageTypes } from '../../services/api'
import {isSafari} from "../../services/utils";

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      image: null
    }
  },
  watch: {
    image: function () {
      this.$emit('update', this.image)
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
      return validImageTypes
    },
    dropHelp: function () {
      return isSafari ? this.$t('_form.image.choose') : this.$t('_form.image.drop_or_choose')
    }
  },
  mounted () {
    this.$emit('update', null)
  }
}
</script>
