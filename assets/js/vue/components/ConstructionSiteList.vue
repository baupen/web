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
          <img :src="constructionSite.imageUrl" :alt="'thumbnail of ' + constructionSite.name">
        </td>
        <td>{{ constructionSite.name }}</td>
        <td>{{ formatConstructionSiteAddress(constructionSite).join(", ") }}</td>
        <td>
          <human-readable-date-time :value="constructionSite.createdAt"/>
        </td>
        <td>
          <button type="button" class="btn btn-toggle" aria-pressed="true"
                  :class="{'active': this.ownsConstructionSite(constructionSite)}"
                  @click="toggleOwnConstructionSite">
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
import Masonry from './ConstructionSitePreviews/Masonry'
import ConstructionSiteCard from "./ConstructionSitePreviews/ConstructionSiteCard";
import HumanReadableDateTime from "./Shared/HumanReadableDateTime";

export default {
  components: {
    HumanReadableDateTime,
    ConstructionSiteCard,
    Masonry
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
      console.log("toggle");
    }
  }
}
</script>
