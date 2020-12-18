<template>
  <div class="card">
    <img class="card-img-top" :src="constructionSite.imageUrl + '?size=preview'" :alt="'image of ' + constructionSite.name">
    <div class="card-body">
      <h2>{{ constructionSite.name }}</h2>
      <p>
        <span class="pre">{{ address.join("\n") }}</span>
      </p>
      <a :href="constructionSiteDashboardHref" class="btn btn-primary">
        {{$t('switch.actions.enter_construction_site')}}
      </a>
    </div>
    <div class="card-footer">
      <small class="text-muted">
        {{ constructionManagerNames.join(", ") }}
      </small>
    </div>
  </div>
</template>

<script>

import { constructionSiteFormatter } from '../../services/formatters'

export default {
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
          .map(id => this.constructionManagers.find(manager => manager["@id"] === id))
          .map(manager => manager.givenName + " " + manager.familyName);
    },
    address: function () {
      return constructionSiteFormatter.address(this.constructionSite)
    },
    constructionSiteDashboardHref: function () {
      return this.constructionSite['@id'].replace("/api", "") + "/dashboard";
    }
  }
}
</script>
