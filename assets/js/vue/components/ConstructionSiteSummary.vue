<template>
  <div>
    <img class="img-fluid" v-if="constructionSite.imageUrl" :src="constructionSite.imageUrl + '?size=preview'" :alt="'image of ' + constructionSite.name">
    <div class="mt-2">
      <h2>{{ constructionSite.name }}</h2>
      <p>
        <span class="pre">{{ address.join("\n") }}</span>
      </p>
    </div>
  </div>
</template>

<script>

import { constructionSiteFormatter } from '../services/formatters'

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
