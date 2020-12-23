<template>
  <div>
    <button-with-modal :title="$t('dispatch.actions.compose_email')" :confirm-title="$t('dispatch.actions.send_email')" :can-confirm="canConfirm" @confirm="confirm">
      <email-edit v-model="email" @valid="canConfirm = $event" />
    </button-with-modal>
  </div>
</template>

<script>

import ButtonWithModal from "./Shared/ButtonWithModal";
import EmailEdit from "./Form/EmailEdit";

export default {
  components: {EmailEdit, ButtonWithModal},
  emits: ['send'],
  data() {
    return {
      email: [],
      show: false,
      canConfirm: true
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: true
    }
  },
  methods: {
    confirm: function () {
      this.$emit('send', this.email)
    }
  }
}
</script>
