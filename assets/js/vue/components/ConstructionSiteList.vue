<template>
  <div>
    <table class="table table-hover table-striped">
      <thead>
      <tr>
        <th class="w-minimal"></th>
        <th>{{$t('construction_site.name')}}</th>
        <th>{{$t('construction_site.address')}}</th>
        <th class="w-minimal"></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="constructionSite in constructionSites" :key="constructionSite['@id']">
        <td>
          <img :src="constructionSite.imageUrl" :alt="'thumbnail of ' + constructionSite.name">
        </td>
        <td>{{constructionSite.name}}</td>
        <td>{{ formatConstructionSiteAddress(constructionSite).join(", ") }}</td>
        <td>
          <button type="button" class="btn btn-toggle active" data-toggle="button" aria-pressed="true" autocomplete="off">
            <div class="handle"></div>
          </button>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

import { constructionSiteFormatter } from '../services/formatters'
import Masonry from './ConstructionSitePreviews/Masonry'
import ConstructionSiteCard from "./ConstructionSitePreviews/ConstructionSiteCard";

export default {
  components: {
    ConstructionSiteCard,
    Masonry
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    }
  },
  methods: {
    formatConstructionSiteAddress: function (constructionSite) {
      return constructionSiteFormatter.address(constructionSite);
    }
  }
}
</script>
