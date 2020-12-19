<template>
  <div id="switch">
    <div class="mb-5">
      <h1>{{ $t("switch.mine") }}</h1>
      <p>{{ $t("switch.mine_help") }}</p>

      <spinner :spin="isLoading">
        <construction-site-previews v-if="memberOfConstructionSites.length > 0"
                                    :construction-sites="memberOfConstructionSites"
                                    :construction-managers="constructionManagers"/>

        <div class="alert alert-info" v-else>
          {{ $t('switch.messages.info.activate_construction_site') }}
        </div>
      </spinner>
    </div>
    <h2>{{ $t("switch.all") }}</h2>
    <p>{{ $t("switch.all_help") }}</p>
    <spinner :spin="isLoading">
      <button @click="show = !show" class="btn btn-primary">{{ $t('switch.actions.create_construction_site') }}</button>
      <transition name="fade">
        <modal v-if="show" @hide="show = false" :title="$t('switch.actions.create_construction_site')">
          <construction-site-add :construction-sites="constructionSiteList" @save="postConstructionSite"/>
        </modal>
      </transition>

      <construction-site-list @remove-self="removeSelfFromConstructionSite"
                              @add-self="addSelfToConstructionSite"
                              :construction-sites="constructionSiteList"
                              :construction-manager-iri="constructionManagerIri"/>
    </spinner>
  </div>
</template>

<script>
import {api} from './services/api';
import ConstructionSitePreviews from "./components/ConstructionSitePreviews";
import ConstructionSiteList from "./components/ConstructionSiteList";
import Modal from "./components/Shared/Modal";
import ConstructionSiteAdd from "./components/ConstructionSiteAdd";

export default {
  components: {ConstructionSiteAdd, Modal, ConstructionSiteList, ConstructionSitePreviews},
  data() {
    return {
      constructionManagerIri: null,
      constructionSites: null,
      constructionManagers: null,
      show: false
    }
  },
  computed: {
    isLoading: function () {
      return this.constructionSites === null || this.constructionManagers === null || this.constructionManagerIri === null;
    },
    memberOfConstructionSites: function () {
      return this.constructionSiteList.filter(constructionSite => constructionSite.constructionManagers.includes(this.constructionManagerIri))
    },
    constructionSiteList: function () {
      return this.constructionSites.filter(constructionSite => !constructionSite.isDeleted)
    },
  },
  methods: {
    postConstructionSite: function (constructionSite) {
      this.show = false
      constructionSite.constructionManagers = [this.constructionManagerIri];
      api.post('/api/construction_sites', constructionSite, this.constructionSites);
    },
    removeSelfFromConstructionSite: function (constructionSite) {
      let constructionManagersWithoutSelf = constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri);
      api.patch(constructionSite, {
        "constructionManagers": constructionManagersWithoutSelf
      });
    },
    addSelfToConstructionSite: function (constructionSite) {
      let constructionManagers = constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri);
      constructionManagers.push(this.constructionManagerIri);
      api.patch(constructionSite, {
        "constructionManagers": constructionManagers
      });
    }
  },
  mounted() {
    api.setupErrorNotifications(this);
    api.loadMe(this);
    api.loadConstructionSites(this);
    api.loadConstructionManagers(this);
  }
}

</script>

<style scoped="true">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>
