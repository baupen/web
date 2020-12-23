<template>
  <div>
    <text-edit id="name" :label="$t('construction_site.name')"
               v-model="modelValue.name"
               @input="emitUpdate"
               @valid="validProperties.name = $event"
               :focus="true">
      <div>
          <p v-if="similarConstructionSiteNames.length > 0" class="text-primary">
          <small>
            {{$t('switch.similar_already_existing_construction_sites')}}: {{ similarConstructionSiteNames.join(",") }}
          </small>
        </p>
      </div>
    </text-edit>


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
import debounce from "lodash.debounce"

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
        distance: this.levenshteinDistance(newName, constructionSite.name)
      })).filter(similarConstructionSite => similarConstructionSite.distance < maxDistanceForWarning)
          .sort(((a, b) => a.distance - b.distance))

      this.similarConstructionSiteNames = similarConstructionSites.map(similarConstructionSite => similarConstructionSite.constructionSite.name).slice(0, 3)
    },
    levenshteinDistance: function (a, b) {
      // source: https://gist.github.com/andrei-m/982927#gistcomment-1797205
      let m = [], i, j, min = Math.min;

      if (!(a && b)) return (b || a).length;

      for (i = 0; i <= b.length; m[i] = [i++]) ;
      for (j = 0; j <= a.length; m[0][j] = j++) ;

      for (i = 1; i <= b.length; i++) {
        for (j = 1; j <= a.length; j++) {
          m[i][j] = b.charAt(i - 1) === a.charAt(j - 1)
              ? m[i - 1][j - 1]
              : m[i][j] = min(
                  m[i - 1][j - 1] + 1,
                  min(m[i][j - 1] + 1, m[i - 1][j] + 1))
        }
      }

      return m[b.length][a.length];
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
