<template>
  <div>
    <button-with-modal-confirm modal-size="lg" :title="$t('dispatch.actions.compose_email')" :confirm-title="sendEmailText" :can-confirm="canConfirm" @confirm="confirm">

      <div class="row">
        <div class="col-2">
          {{ $t('email.to') }}
        </div>
        <div class="col">
          {{ receivers.join(", ") }}

          <boolean-edit class="mb-0 mt-2"
              id="self-bcc"
              :label="$t('email.self_bcc')"
              v-model="email.selfBcc" />
        </div>
      </div>

      <hr/>

      <email-content-edit v-model="email" @valid="canConfirm = $event">
        <template v-slot:textarea="">
          <small class="form-text text-muted">{{$t('dispatch.resolve_link_is_appended')}}</small>
        </template>
      </email-content-edit>
    </button-with-modal-confirm>
  </div>
</template>

<script>

import EmailContentEdit from "./Edit/EmailContentEdit";
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";
import BooleanEdit from './Edit/Widget/BooleanEdit'
import InlineTextEdit from './Edit/Widget/InlineTextEdit'

export default {
  components: {
    InlineTextEdit,
    BooleanEdit,
    ButtonWithModalConfirm, EmailContentEdit},
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
  computed: {
    receivers: function() {
      return this.craftsmen.map(craftsman => craftsman.contactName + ' (' + craftsman.company + ')')
    },
    sendEmailText: function () {
      return this.$tc('dispatch.actions.send_emails', this.craftsmen.length, {'count': this.craftsmen.length})
    }
  },
  methods: {
    confirm: function () {
      this.$emit('send', this.email)
    }
  }
}
</script>
