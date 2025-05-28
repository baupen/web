<template>
  <form-field for-id="image" :label="$t('_form.image.label')" :required="false">
    <div class="row g-2">
      <div :class="{'col-md-8': shownUrl, 'col-md-12': !shownUrl}">
        <dropzone
            class="h-100"
            v-if="!image"
            id="image" :help="dropHelp"
            :valid-file-types="validFileTypes"
            @input="image = $event[0]"/>
        <input v-if="image" id="image" class="form-control" type="text" readonly="readonly"
               :class="{'is-valid': imageIsValid, 'is-invalid': !imageIsValid }"
               :value="image.name">
        <a class="btn-link clickable" v-if="image" @click="image = null">
          {{ $t('_form.reset') }}
        </a>
      </div>
      <div class="col-md-4" v-if="shownUrl">
        <img class="img-fluid" :src="shownUrl" alt="preview">
      </div>
    </div>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import {validImageTypes} from '../../services/api'
import {isSafari} from "../../services/utils";

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      image: null,
    }
  },
  props: {
    currentUrl: {
      type: String,
      required: false,
    },
  },
  watch: {
    image: function () {
      this.$emit('update', this.image)
    },
  },
  computed: {
    shownUrl: function() {
      if (!this.image) {
        return this.currentUrl
      }

      return URL.createObjectURL(this.image);
    },
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
