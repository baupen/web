<template>
  <form-field for-id="name" :label="$t('map.name')">
    <input id="name" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.name.dirty && !fields.name.errors.length, 'is-invalid': fields.name.dirty && fields.name.errors.length }"
           v-model="map.name"
           @blur="fields.name.dirty = true"
           @input="validate('name')">
    <invalid-feedback :errors="fields.name.errors" />
  </form-field>

  <form-field for-id="parent" :label="$t('map.parent')">
    <select class="form-select"
            :class="{'is-valid': fields.parent.dirty && !fields.parent.errors.length, 'is-invalid': fields.parent.dirty && fields.parent.errors.length }"
            v-model="map.parent"
            @change="validate('parent')"
    >
      <option :value="null">{{ $t('map.parent_not_set_name') }}</option>
      <option disabled></option>
      <option v-for="mapContainer in mapContainers" :value="mapContainer.entity['@id']"
              :key="mapContainer.entity['@id']" :disabled="mapContainer.entity === template">
        {{ '&nbsp;'.repeat(mapContainer.level) }} {{ mapContainer.entity.name }}
      </option>
    </select>
    <invalid-feedback :errors="fields.parent.errors" />
  </form-field>
</template>

<script>

import { createField, requiredRule, validateField, validateFields, changedFieldValues } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import { mapTransformer } from '../../services/transformers'

export default {
  components: {
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        name: createField(requiredRule()),
        parent: createField()
      },
      map: {
        name: null,
        parent: null,
      }
    }
  },
  props: {
    template: {
      type: Object
    },
    maps: {
      type: Array,
      default: []
    }
  },
  watch: {
    updatePayload: {
      deep: true,
      handler: function () {
        this.$emit('update', this.updatePayload)
      }
    }
  },
  methods: {
    validate: function (field) {
      if (field === 'parent') {
        this.fields[field].dirty = true
      }
      validateField(this.fields[field], this.map[field])
    },
    setMapFromTemplate: function () {
      if (this.template) {
        this.map = Object.assign({}, this.template)
      }

      validateFields(this.fields, this.map)
    }
  },
  computed: {
    mapContainers: function () {
      return mapTransformer.orderedList(this.maps, mapTransformer.PROPERTY_LEVEL)
    },
    updatePayload: function () {
      if (this.fields.name.errors.length ||
          this.fields.parent.errors.length) {
        return null
      }

      return changedFieldValues(this.fields, this.map, this.template)
    },
  },
  mounted () {
    this.setMapFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
