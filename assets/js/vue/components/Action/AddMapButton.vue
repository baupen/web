<template>
  <button-with-modal-confirm
      :button-disabled="posting || !maps" :title="$t('_action.add_map.title')" :can-confirm="canConfirm"
      @confirm="confirm" :repeat-confirm-label="$t('_action.add_map.add_more')">
    <map-form :maps="maps" :proposed-name="proposedName" @update="post = $event" />
    <hr/>
    <file-form @update="file = $event" :pdf-mode="true" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapForm from '../Form/MapForm'
import FileForm from '../Form/FileForm'

export default {
  emits: ['added'],
  components: {
    FileForm,
    MapForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      file: null,
      post: null,
      posting: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      required: false
    },
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    },
    proposedName: function () {
      return this.file ? this.file.name.substr(0, this.file.name.indexOf(".")) : null
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const payload = Object.assign({}, this.post, { constructionSite: this.constructionSite['@id'] })

      let successMessage = this.$t('_action.add_map.added')

      if (!this.file) {
        api.postMap(payload, successMessage)
            .then(map => {
              this.posting = false
              this.$emit('added', map)
            })
        return
      }

      api.postMap(payload)
          .then(map => {
            api.postMapFile(map, this.file, successMessage)
                .then(_ => {
                  this.posting = false
                  this.$emit('added', map)
                })
          })
    }
  }
}
</script>
