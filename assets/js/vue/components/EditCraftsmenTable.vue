<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('craftsman.company') }}</th>
      <th>{{ $t('craftsman.trade') }}</th>
      <th>{{ $t('craftsman.contact_name') }}</th>
      <th>{{ $t('craftsman.email') }}</th>
      <th>{{ $t('craftsman.emailCCs') }}</th>
      <th class="w-minimal" />
    </tr>
    </thead>
    <tbody>
    <tr v-if="!craftsmen">
      <td colspan="100">
        <loading-indicator />
      </td>
    </tr>
    <tr v-else v-for="craftsman in craftsmen">
      <td>{{ craftsman.company }}</td>
      <td>{{ craftsman.trade }}</td>
      <td>{{ craftsman.contactName }}</td>
      <td>{{ craftsman.email }}</td>
      <td>{{ craftsman.emailCCs.length ? craftsman.emailCCs.join('\n') : '-' }}</td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-craftsman-button :craftsman="craftsman" />
          <delete-craftsman-button :construction-site="constructionSite" :craftsman="craftsman" />
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import { api } from '../services/api'
import LoadingIndicator from './View/LoadingIndicator'
import EditCraftsmanButton from './Action/EditCraftsmanButton'
import DeleteCraftsmanButton from './Action/RemoveCraftsmanButton'
import ButtonWithModalConfirm from './Behaviour/ButtonWithModalConfirm'

export default {
  components: {
    ButtonWithModalConfirm,
    DeleteCraftsmanButton,
    EditCraftsmanButton,
    LoadingIndicator
  },
  data () {
    return {
      craftsmen: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {},
  mounted () {
    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => this.craftsmen = craftsmen)
  }
}
</script>
