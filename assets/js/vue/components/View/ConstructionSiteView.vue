<template>
  <div class="card shadow">
    <img class="card-img-top max-heighted" v-if="constructionSite.imageUrl" :src="constructionSite.imageUrl + '?size=full'"
         :alt="'image of ' + constructionSite.name">
    <div class="card-body">
      <h5 class="card-title">{{ constructionSite.name }}</h5>
      <p class="card-text">
        <span class="pre">{{ address.join("\n") }}</span>
      </p>
    </div>
  </div>
</template>

<script>

import {constructionSiteFormatter} from '../../services/formatters'

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

<style scoped>
.max-heighted {
  max-height: 20em;
  object-fit: cover;
}
</style>
