<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <img class="img-fluid" v-if="constructionSite.imageUrl" :src="constructionSite.imageUrl + '?size=preview'"
               :alt="'image of ' + constructionSite.name">
        </div>
        <div class="col-md-9">
          <h2>{{ constructionSite.name }}</h2>
          <p>
            <span class="pre">{{ address.join('\n') }}</span>
          </p>
          <enter-construction-site :construction-site="constructionSite" />
        </div>
      </div>
    </div>
    <div class="card-footer">
      <small class="text-muted">
        {{ constructionManagerNames.join(', ') }}
      </small>
    </div>
  </div>
</template>

<script>

import { constructionSiteFormatter, constructionManagerFormatter } from '../../services/formatters'
import EnterConstructionSite from '../Action/EnterConstructionSite'

export default {
  components: { EnterConstructionSite },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    }
  },
  computed: {
    constructionManagerNames: function () {
      return this.constructionSite.constructionManagers
          .map(id => this.constructionManagers.find(manager => manager['@id'] === id))
          .filter(m => m)
          .map(manager => constructionManagerFormatter.name(manager))
    },
    address: function () {
      return constructionSiteFormatter.address(this.constructionSite)
    }
  }
}
</script>
