<template>
  <form-field for-id="name" :label="$t('construction_site.name')">
    <input id="name" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.name.dirty && !fields.name.errors.length, 'is-invalid': fields.name.dirty && fields.name.errors.length }"
           @blur="fields.name.dirty = true"
           v-model="constructionSite.name"
           @input="validate('name')">
    <invalid-feedback :errors="fields.name.errors" />
    <p v-if="similarConstructionSiteNames.length > 0" class="text-primary">
      <small>
        {{ $t('_form.construction_site.similar_already_existing_construction_sites') }}:
        {{ similarConstructionSiteNames.join(',') }}
      </small>
    </p>
  </form-field>

  <form-field for-id="streetAddress" :label="$t('construction_site.street_address')">
    <input id="streetAddress" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.streetAddress.dirty && !fields.streetAddress.errors.length, 'is-invalid': fields.streetAddress.dirty && fields.streetAddress.errors.length }"
           @blur="fields.streetAddress.dirty = true"
           v-model="constructionSite.streetAddress"
           @input="validate('streetAddress')">
    <invalid-feedback :errors="fields.streetAddress.errors" />
  </form-field>

  <div class="row">
    <form-field class="col-md-4" for-id="postalCode" :label="$t('construction_site.postal_code')">
      <input id="postalCode" class="form-control" type="number" required="required"
             :class="{'is-valid': fields.postalCode.dirty && !fields.postalCode.errors.length, 'is-invalid': fields.postalCode.dirty && fields.postalCode.errors.length }"
             @blur="fields.postalCode.dirty = true"
             v-model.number="constructionSite.postalCode"
             @input="validate('postalCode')">
      <invalid-feedback :errors="fields.postalCode.errors" />
    </form-field>

    <form-field class="col-md-8" for-id="locality" :label="$t('construction_site.locality')">
      <input id="locality" class="form-control" type="text" required="required"
             :class="{'is-valid': fields.locality.dirty && !fields.locality.errors.length, 'is-invalid': fields.locality.dirty && fields.locality.errors.length }"
             @blur="fields.locality.dirty = true"
             v-model="constructionSite.locality"
             @input="validate('locality')">
      <invalid-feedback :errors="fields.locality.errors" />
    </form-field>

  </div>
</template>

<script>
import debounce from 'lodash.debounce'
import { levenshteinDistance } from '../../services/algorithms'
import { changedFieldValues, createField, requiredRule, validateField, validateFields } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'

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
        streetAddress: createField(requiredRule()),
        postalCode: createField(requiredRule()),
        locality: createField(requiredRule()),
      },
      constructionSite: {
        name: null,
        streetAddress: null,
        postalCode: null,
        locality: null,
      },
      similarConstructionSiteNames: []
    }
  },
  props: {
    template: {
      type: Object
    },
    constructionSites: {
      type: Array,
      required: false,
      default: []
    }
  },
  watch: {
    updatePayload: {
      deep: true,
      handler: function () {
        this.$emit('update', this.updatePayload)
      }
    },
    template: function () {
      this.setConstructionSiteFromTemplate()
    },
    'constructionSite.name': debounce(function (newVal) {
      this.determineSimilarConstructionSiteNames(newVal)
    }, 200)
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.constructionSite[field])
    },
    setConstructionSiteFromTemplate: function () {
      if (this.template) {
        this.constructionSite = Object.assign({}, this.template)
      }

      validateFields(this.fields, this.constructionSite)
    },
    determineSimilarConstructionSiteNames: function (newName) {
      if (!newName || newName.length < 4 || !this.constructionSitesWithoutTemplate) {
        return this.similarConstructionSiteNames = []
      }

      const maxDistanceForWarning = Math.sqrt(newName.length)

      let similarConstructionSites = this.constructionSitesWithoutTemplate.map(constructionSite => ({
        constructionSite,
        distance: levenshteinDistance(newName.toLowerCase(), constructionSite.name.toLowerCase())
      }))

      similarConstructionSites = similarConstructionSites.filter(similarConstructionSite => similarConstructionSite.distance < maxDistanceForWarning)
          .sort(((a, b) => a.distance - b.distance))

      this.similarConstructionSiteNames = similarConstructionSites.map(similarConstructionSite => similarConstructionSite.constructionSite.name)
          .slice(0, 3)
    },
  },
  computed: {
    constructionSitesWithoutTemplate: function () {
      return this.constructionSites.filter(c => c !== this.template)
    },
    updatePayload: function () {
      if (this.fields.name.errors.length ||
          this.fields.streetAddress.errors.length ||
          this.fields.postalCode.errors.length ||
          this.fields.locality.errors.length) {
        return null
      }

      return changedFieldValues(this.fields, this.constructionSite, this.template)
    }
  },
  mounted () {
    this.setConstructionSiteFromTemplate()
    this.$emit('update', this.updatePayload)
  }
}
</script>
