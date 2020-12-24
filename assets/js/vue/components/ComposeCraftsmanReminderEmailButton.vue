<template>
  <div>
    <button-with-modal-confirm modal-size="lg" :title="$t('dispatch.actions.compose_email')" :confirm-title="$t('dispatch.actions.send_email')" :can-confirm="canConfirm" @confirm="confirm">
      <email-edit v-model="email" @valid="canConfirm = $event" />
    </button-with-modal-confirm>
  </div>
</template>

<script>

import EmailEdit from "./Edit/EmailEdit";
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";

export default {
  components: {ButtonWithModalConfirm, EmailEdit},
  emits: ['send'],
  data() {
    return {
      email: {},
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
      // TODO: check bcc really works as intendede
      console.log(this.email)
      console.log(this.craftsmen)
      this.craftsmen.forEach(craftsman => {
        const personalMail = Object.assign({type: 4}, this.email, {receiver: craftsman['@id']});
        console.log(personalMail)
        this.$emit('send', personalMail)
      })
    }
  }
}
</script>
