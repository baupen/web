<template>
  <table class="table table-hover border shadow">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('craftsman.trade') }}</th>
      <th>{{ $t('craftsman.company') }}</th>
      <th class="border-start">{{ $t('craftsman.contact_name') }}</th>
      <th>{{ $t('craftsman.email') }}</th>
      <th>{{ $t('craftsman.emailCCs') }}</th>
      <th class="w-minimal" />
    </tr>
    </thead>
    <tbody>
    <loading-indicator-table-body v-if="!orderedCraftsmen" />
    <tr v-else-if="orderedCraftsmen.length === 0">
      <td colspan="99">
        <p class="text-center">{{ $t('_view.no_craftsmen') }}</p>
      </td>
    </tr>
    <tr v-else v-for="craftsman in orderedCraftsmen">
      <td>{{ craftsman.trade }}</td>
      <td>
        {{ craftsman.company }}
      </td>
      <td class="border-start">
        {{ craftsman.contactName }}
      </td>
      <td>{{ craftsman.email }}</td>
      <td>{{ craftsman.emailCCs.length ? craftsman.emailCCs.join(', ') : '-' }}</td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-craftsman-button :craftsman="craftsman" />
          <remove-craftsman-button :construction-site="constructionSite" :craftsman="craftsman" />
        </div>
      </td>
    </tr>
    </tbody>
    <caption>
    <slot name="caption"></slot>
    </caption>
  </table>
</template>

<script>

import EditCraftsmanButton from '../Action/EditCraftsmanButton'
import RemoveCraftsmanButton from '../Action/RemoveCraftsmanButton'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'

export default {
  components: {
    LoadingIndicatorTableBody,
    RemoveCraftsmanButton,
    EditCraftsmanButton
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    craftsmen: {
      type: Array,
      required: false
    }
  },
  computed: {
    orderedCraftsmen: function () {
      if (!this.craftsmen) {
        return null;
      }

      return this.craftsmen.sort((a, b) => a.trade.localeCompare(b.trade))
    }
  },
}
</script>
