<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('construction_manager.email') }}</th>
      <th>{{ $t('construction_manager.name') }}</th>
      <th class="w-minimal" />
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!orderedConstructionManagers" />
    <tr v-else v-for="constructionManager in orderedConstructionManagers">
      <td>{{ constructionManager.email }}</td>
      <td>
        {{ getName(constructionManager) }}
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <dissociate-construction-manager-button :construction-site="constructionSite" :constructionManager="constructionManager" />
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import EditCraftsmanButton from '../Action/EditCraftsmanButton'
import RemoveCraftsmanButton from '../Action/RemoveCraftsmanButton'
import TableBodyLoadingIndicator from '../Library/View/LoadingIndicatorTableBody'
import { constructionManagerFormatter } from '../../services/formatters'
import DissociateConstructionManagerButton from '../Action/DissociateConstructionManagerButton'

export default {
  components: {
    DissociateConstructionManagerButton,
    TableBodyLoadingIndicator,
    RemoveCraftsmanButton,
    EditCraftsmanButton
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: false
    }
  },
  computed: {
    orderedConstructionManagers: function () {
      if (!this.constructionManagers) {
        return null;
      }

      return this.constructionManagers.sort((a, b) => a.email.localeCompare(b.email))
    }
  },
  methods: {
    getName: function (manager) {
      return constructionManagerFormatter.name(manager)
    }
  }
}
</script>
