<template>
  <div>
    <text-edit id="name" :label="$t('construction_site.name')" :focus="true"
               v-model="modelValue.name"
               @input="emitUpdate"
               @valid="validProperties.name = $event">
      <div>
        <p v-if="similarConstructionSiteNames.length > 0" class="text-primary">
          <small>
            {{ $t('switch.similar_already_existing_construction_sites') }}: {{
              similarConstructionSiteNames.join(",")
            }}
          </small>
        </p>
      </div>
    </text-edit>

    <text-edit id="streetAddress" :label="$t('construction_site.street_address')"
               v-model="modelValue.streetAddress"
               @input="emitUpdate"
               @valid="validProperties.streetAddress = $event"/>

    <div class="form-row">
      <text-edit class="col-md-4" id="postalCode" :label="$t('construction_site.postal_code')" type="number"
                 v-model.number="modelValue.postalCode"
                 @input="emitUpdate"
                 @valid="validProperties.postalCode = $event"/>

      <text-edit class="col-md-8" id="locality" :label="$t('construction_site.locality')"
                 v-model="modelValue.locality"
                 @input="emitUpdate"
                 @valid="validProperties.locality = $event"/>
    </div>
  </div>
</template>

<script>
import debounce from "lodash.debounce"
import FormField from "./Layout/FormField";
import InputWithFeedback from "./Input/InputWithFeedback";
import TextEdit from "./Widget/TextEdit";
import { levenshteinDistance } from '../../services/algorithms'

export default {
  components: {TextEdit, InputWithFeedback, FormField},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      validProperties: {
        name: false,
        streetAddress: false,
        postalCode: false,
        locality: false,
      },
      similarConstructionSiteNames: []
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
    },
    'modelValue.name': debounce(function (newVal) {
      this.determineSimilarConstructionSiteNames(newVal)
    }, 200)
  },
  methods: {
    emitUpdate: function () {
      this.$emit('update:modelValue', {...this.modelValue})
    },
    emitValid: function () {
      this.$emit('valid', this.isValid)
    },
    determineSimilarConstructionSiteNames: function (newName) {
      if (!newName || newName.length < 4) {
        return this.similarConstructionSiteNames = [];
      }

      const maxDistanceForWarning = Math.sqrt(newName.length)

      let similarConstructionSites = this.constructionSites.map(constructionSite => ({
        constructionSite,
        distance: levenshteinDistance(newName.toLowerCase(), constructionSite.name.toLowerCase())
      }));

      similarConstructionSites = similarConstructionSites.filter(similarConstructionSite => similarConstructionSite.distance < maxDistanceForWarning)
          .sort(((a, b) => a.distance - b.distance))

      this.similarConstructionSiteNames = similarConstructionSites.map(similarConstructionSite => similarConstructionSite.constructionSite.name).slice(0, 3)
    },
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
