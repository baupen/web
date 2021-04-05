<template>
  <a href="#" @click.stop="toggleCanEdit">
    <font-awesome-icon v-if="craftsman.canEdit" :icon="['fal', 'unlock']" />
    <font-awesome-icon class="text-danger" v-else :icon="['fal', 'lock']" />
  </a>
</template>

<script>

import { api } from '../../services/api'

export default {
  props: {
    craftsman: {
      type: Object,
      required: true
    }
  },
  methods: {
    toggleCanEdit: function () {
      const patch = { 'canEdit': !this.craftsman.canEdit }
      const message = this.craftsman.canEdit ? this.$t('_action.toggle_can_edit.no_edit_anymore') : this.$t('_action.toggle_can_edit.can_edit')
      api.patch(this.craftsman, patch, message)
    }
  }
}
</script>
