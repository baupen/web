<template>
  <button-with-modal-confirm
      :button-disabled="importing" :title="$t('_action.import_craftsmen.title')"
      :confirm-title="$tc('_action.import_craftsmen.confirm', this.pendingPost.length + this.pendingPatch.length)" :can-confirm="canConfirm"
      @shown="reset"
      @confirm="confirm">

    <craftsman-import-form :craftsmen="craftsmen" @imported="importedCraftsmen = $event"/>

    <template v-if="pendingPatch.length > 0">
      <p class="alert alert-warning">
        {{ $tc('_action.import_craftsmen.matching_entries_found', pendingPatch.length) }}
      </p>
    </template>
    <template v-else-if="importedCraftsmen">
      <p class="alert alert-success">
        {{ $t('_action.import_craftsmen.all_entries_match') }}
      </p>
    </template>
  </button-with-modal-confirm>
</template>

<script>
import {api} from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import FormField from '../Library/FormLayout/FormField'
import CraftsmanImportForm from '../Form/CraftsmanImportForm'
import {displaySuccess} from '../../services/notifiers'

export default {
  components: {
    CraftsmanImportForm,
    FormField,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  emits: ['imported'],
  data() {
    return {
      importedCraftsmen: null,
      importing: false,
      pendingPatch: [],
      pendingPost: [],
    }
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
  watch: {
    importedCraftsmen: function () {
      if (this.importedCraftsmen === null) {
        return
      }

      this.pendingPatch = []
      this.pendingPost = []

      this.importedCraftsmen.forEach(importedCraftsman => {
        let existingCraftsman = this.craftsmen.find(c => c.email === importedCraftsman.email);
        if (existingCraftsman) {
          let foundDifference = false
          for (const entry in importedCraftsman) {
            if (Object.prototype.hasOwnProperty.call(importedCraftsman, entry)) {
              if (existingCraftsman[entry] !== importedCraftsman[entry]) {
                if (Array.isArray(existingCraftsman[entry]) && Array.isArray(importedCraftsman[entry])
                    && existingCraftsman[entry].length === importedCraftsman[entry].length
                    && existingCraftsman[entry].every(e => importedCraftsman[entry].includes(e))) {
                  continue; // not different
                }

                foundDifference = true
                break
              }
            }
          }

          if (foundDifference) {
            this.pendingPatch.push({craftsman: existingCraftsman, patch: importedCraftsman})
          }
        } else {
          this.pendingPost.push(Object.assign({constructionSite: this.constructionSite['@id']}, importedCraftsman))
        }
      })
    }
  },
  computed: {
    canConfirm: function () {
      return this.pendingPost.length > 0 || this.pendingPatch.length > 0
    }
  },
  methods: {
    reset: function () {
      this.pendingPatch = []
      this.pendingPost = []
    },
    confirm: function () {
      this.importing = true
      this.doImport()
    },
    doImport: async function () {
      while (this.pendingPatch.length > 0) {
        const payload = this.pendingPatch[0]
        await api.patch(payload.craftsman, payload.patch)
        this.pendingPatch.shift()
      }
      while (this.pendingPost.length > 0) {
        const payload = this.pendingPost[0]
        await api.postCraftsman(payload)
        this.pendingPost.shift()
      }

      displaySuccess(this.$t('_action.import_craftsmen.imported'))
      this.$emit('imported')
      this.importing = false
    }
  }
}
</script>
