<template>
  <div>
    <img class="img-fluid shadow" v-if="constructionSite.imageUrl" :src="constructionSite.imageUrl + '?size=full'"
         :alt="'image of ' + constructionSite.name">
    <h4 class="mt-3">{{ constructionSite.name }}</h4>
    <p>
      <span class="pre">{{ address.join("\n") }}</span>
    </p>
  </div>
</template>

<script>

import {constructionSiteFormatter} from '../services/formatters'

export default {
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    address: function () {
      return constructionSiteFormatter.address(this.constructionSite)
    },
  }
}
</script>
