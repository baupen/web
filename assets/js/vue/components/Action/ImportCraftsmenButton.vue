<template>
  <button-with-modal-confirm
      :button-disabled="importing" :title="$t('_action.import_craftsmen.title')"
      :confirm-title="$tc('_action.import_craftsmen.confirm', this.pendingPost.length)" :can-confirm="canConfirm"
      @shown="reset"
      @confirm="confirm">

    <craftsman-import-form :craftsmen="craftsmen" @imported="importedCraftsmen = $event" />

    <template v-if="matchingEntriesFound > 0">
      <p class="alert alert-warning">
        {{ $tc('_action.import_craftsmen.matching_entries_found', matchingEntriesFound) }}
      </p>
    </template>
  </button-with-modal-confirm>
</template>

<script>
import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import FormField from '../Library/FormLayout/FormField'
import CraftsmanImportForm from '../Form/CraftsmanImportForm'
import { displaySuccess } from '../../services/notifiers'

export default {
  components: {
    CraftsmanImportForm,
    FormField,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  emits: ['imported'],
  data () {
    return {
      importedCraftsmen: null,
      importing: false,
      pendingPost: [],
      matchingEntriesFound: 0
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
      this.matchingEntriesFound = 0
      this.pendingPost = []

      this.importedCraftsmen.forEach(importedCraftsman => {
        if (this.craftsmen.find(c => c.email === importedCraftsman.email)) {
          this.matchingEntriesFound++
        }
        this.pendingPost.push(Object.assign({ constructionSite: this.constructionSite['@id'] }, importedCraftsman))
      })
    }
  },
  computed: {
    canConfirm: function () {
      return this.pendingPost.length > 0
    }
  },
  methods: {
    reset: function () {
      this.pendingPost = []
      this.matchingEntriesFound = 0
    },
    confirm: function () {
      this.importing = true
      this.continueImport()
    },
    continueImport: function () {
      const payload = this.pendingPost[0]
      api.postCraftsman(payload)
          .then(_ => {
                this.pendingPost.shift()

                if (this.pendingPost.length === 0) {
                  displaySuccess(this.$t('_action.import_craftsmen.imported'))
                  this.$emit('imported')
                  this.importing = false
                } else {
                  this.continueImport()
                }
              }
          )
    }
  }
}
</script>
