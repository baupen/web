<template>
  <button-with-modal-confirm
      :button-disabled="patching" :title="$t('actions.edit_map')" :can-confirm="canConfirm"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <map-form :template="map" :maps="maps" @update="patch = $event" />
    <file-form @update="file = $event" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapForm from '../Form/MapForm'
import FileForm from '../Form/FileForm'

export default {
  components: {
    FileForm,
    MapForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      file: null,
      patch: null,
      patching: false
    }
  },
  props: {
    map: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      required: true
    },
  },
  computed: {
    canConfirm: function () {
      return this.pendingChanges > 0
    },
    pendingChanges: function () {
      let count = this.pendingPatch ? 1 : 0
      count += this.file ? 1 : 0

      return count
    },
    pendingPatch: function () {
      return this.patch && Object.keys(this.patch).length
    }
  },
  methods: {
    confirm: function () {
      this.patching = true

      if (this.pendingPatch) {
        api.patch(this.map, this.patch, this.$t('actions.messages.success.map_added'))
            .then(_ => {
              this.patch = null
              this.patching = this.pendingChanges > 0
            })
      }
      if (this.file) {
        api.postMapFile(this.map, this.file, this.$t('actions.messages.success.map_file_replaced'))
            .then(_ => {
              this.file = null
              this.patching = this.pendingChanges > 0
            })
      }
    }
  }
}
</script>
