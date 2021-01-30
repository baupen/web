<template>
  <button-with-modal-confirm
      :button-disabled="importing" :title="$t('actions.import_craftsmen')"
      :confirm-title="$t('actions.import')" :can-confirm="canConfirm"
      @confirm="confirm">

    <craftsman-import-form :craftsmen="craftsmen" @imported="importedCraftsmen = $event" />

    <template v-if="transaction">
      <p  v-if="transactionMessages.length" class="alert alert-info white-space-pre-line">
        {{ transactionMessages.join('\n') }} <br/>
        <small>
            {{ $t('import_craftsmen.matching_by_email')}}
        </small>
      </p>
    </template>
  </button-with-modal-confirm>
</template>

<script>
import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import CraftsmanImportForm from '../Form/CraftsmanImportForm'
import { displaySuccess } from '../../services/notifiers'

export default {
  components: {
    CraftsmanImportForm,
    Dropzone,
    FormField,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  emits: ['imported'],
  data () {
    return {
      importedCraftsmen: null,
      importing: false,
      importTransaction: null
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
  computed: {
    canConfirm: function () {
      return !!this.transaction
    },
    transaction: function () {
      if (!this.importedCraftsmen || !this.craftsmen) {
        return null
      }

      let patch = []
      let post = []
      this.importedCraftsmen.forEach(importedCraftsman => {
        let existing = this.craftsmen.find(c => c.email === importedCraftsman.email)
        if (!existing) {
          post.push(importedCraftsman)
        } else {
          patch.push({craftsman: existing, patch: importedCraftsman})
        }
      })

      return {
        post,
        patch
      }
    },
    transactionMessages: function () {
      let messages = []
      if (this.transaction.post.length) {
        messages.push(this.$tc('import_craftsmen.added', this.transaction.post.length ))
      }
      if (this.transaction.patch.length) {
        messages.push(this.$tc('import_craftsmen.overwritten', this.transaction.patch.length ))
      }

      return messages
    }
  },
  methods: {
    confirm: function () {
      this.$emit('edit', this.patch)

      this.importing = true
      this.importTransaction = Object.assign({}, this.transaction);
      this.continueImport()
    },
    continueImport: function () {
      if (this.importTransaction.post.length) {
        let currentPost = this.importTransaction.post[0];
        const postWithConstructionSite = Object.assign({}, currentPost,{constructionSite: this.constructionSite['@id']})
        api.postCraftsman(postWithConstructionSite)
            .then(_ => {
              this.importTransaction.post = this.importTransaction.post.filter(e => e !== currentPost)
              this.continueImport()
            })
      } else if (this.importTransaction.patch.length) {
        let currentPatch = this.importTransaction.patch[0];
        api.patch(currentPatch.craftsman, currentPatch.patch)
            .then(_ => {
              this.importTransaction.patch = this.importTransaction.patch.filter(e => e !== currentPatch)
              this.continueImport()
            })
      } else {
        displaySuccess(this.$t('import_craftsmen.import_finished'))
        this.importing = false
        this.$emit("imported")
      }
    }
  }
}
</script>
