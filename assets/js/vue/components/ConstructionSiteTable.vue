<template>
  <div>
    <table class="table table-hover table-striped">
      <thead>
      <tr>
        <th class="w-minimal"></th>
        <th>{{ $t('construction_site.name') }}</th>
        <th>{{ $t('construction_site.address') }}</th>
        <th>{{ $t('construction_site.created_at') }}</th>
        <th class="w-minimal"></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="constructionSite in constructionSites" :key="constructionSite['@id']">
        <td>
          <lightbox :src="constructionSite.imageUrl" :src-full="constructionSite.imageUrl + '?size=full'"
                    :alt="'thumbnail of ' + constructionSite.name" />
        </td>
        <td>{{ constructionSite.name }}</td>
        <td>{{ formatConstructionSiteAddress(constructionSite).join(", ") }}</td>
        <td>
          <human-readable-date-time :value="constructionSite.createdAt"/>
        </td>
        <td>
          <button type="button" class="btn btn-toggle" aria-pressed="true"
                  :class="{'active': ownsConstructionSite(constructionSite)}"
                  @click="toggleOwnConstructionSite(constructionSite)">
            <div class="handle"></div>
          </button>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

import {constructionSiteFormatter} from '../services/formatters'
import Masonry from './Behaviour/Masonry'
import Lightbox from './Behaviour/Lightbox'
import ConstructionSiteCard from "./ConstructionSiteCard";
import HumanReadableDateTime from "./View/HumanReadableDateTime";

export default {
  emits: ['add-self', 'remove-self'],
  components: {
    HumanReadableDateTime,
    ConstructionSiteCard,
    Masonry,
    Lightbox
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  methods: {
    formatConstructionSiteAddress: function (constructionSite) {
      return constructionSiteFormatter.address(constructionSite);
    },
    ownsConstructionSite: function (constructionSite) {
      return constructionSite.constructionManagers.includes(this.constructionManagerIri);
    },
    toggleOwnConstructionSite: function (constructionSite) {
      if (!this.ownsConstructionSite(constructionSite)) {
        this.$emit('add-self', constructionSite)
      } else {
        this.$emit('remove-self', constructionSite);
      }
    }
  }
}
</script>
