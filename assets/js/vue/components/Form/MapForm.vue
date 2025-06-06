<template>
  <form-field for-id="name" :label="$t('map.name')">
    <input id="name" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.name.dirty && !fields.name.errors.length, 'is-invalid': fields.name.dirty && fields.name.errors.length }"
           v-model="map.name"
           @blur="fields.name.dirty = true"
           @input="validate('name')">
    <invalid-feedback :errors="fields.name.errors" />
  </form-field>

  <template  v-if="mapContainers.length > 0">
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
          {{ '&nbsp;'.repeat(mapContainer.level*2) }}{{ mapContainer.entity.name }}
        </option>
      </select>
      <invalid-feedback :errors="fields.parent.errors" />
    </form-field>

    <form-field for-id="parent" :label="$t('_form.map.preview_tree')" v-if="map.name && previewMapContainers.length > 0" :required="false">
      <span class="bg-secondary-subtle p-2 d-block">
        <span class="d-block" v-if="previewIndexStart > 0">
          {{ '&nbsp;'.repeat(previewMapContainers[previewIndexStart-1].level*2) }}...
        </span>
        <span class="d-block" v-for="mapContainer in previewMapContainers.slice(previewIndexStart, previewIndex)" :key="mapContainer.entity['@id']">
          {{ '&nbsp;'.repeat(mapContainer.level*2) }}{{ mapContainer.entity.name }}
        </span>
        <span class="d-block">
          {{ '&nbsp;'.repeat(previewMapContainers[previewIndex].level*2) }}<b>{{ previewMapContainers[previewIndex].entity.name }}</b>
        </span>
        <span class="d-block" v-for="mapContainer in previewMapContainers.slice(previewIndex+1, previewIndexEnd)" :key="mapContainer.entity['@id']">
          {{ '&nbsp;'.repeat(mapContainer.level*2) }}{{ mapContainer.entity.name }}
        </span>
        <span class="d-block" v-if="previewIndexEnd < previewMapContainers.length - 1">
          {{ '&nbsp;'.repeat(previewMapContainers[previewIndexEnd+1].level*2) }}...
        </span>
      </span>
    </form-field>
  </template>
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
    proposedName: {
      type: String,
      required: false
    },
    maps: {
      type: Array,
      default: []
    }
  },
  watch: {
    proposedName: function () {
      if (!this.map.name) {
        this.map.name = this.proposedName
        this.fields.name.dirty = true
        this.validate('name')
      }
    },
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
    previewMapContainers: function () {
      const maps = this.template ? this.maps.filter(m => m['@id'] !== this.template['@id']) : [...this.maps];
      maps.push(this.map)
      return mapTransformer.orderedList(maps, mapTransformer.PROPERTY_LEVEL)
    },
    previewIndexStart: function () {
      return Math.max(0, this.previewIndex - 4)
    },
    previewIndex: function () {
      return this.previewMapContainers.findIndex(mapContainer => mapContainer.entity === this.map)
    },
    previewIndexEnd: function () {
      return Math.min(this.previewIndex + 5, this.previewMapContainers.length - 1)
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
