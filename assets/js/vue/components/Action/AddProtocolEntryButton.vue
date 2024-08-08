<template>
  <button-with-modal-confirm
      :title="$t('_action.add_protocol_entry.title')"
      :button-disabled="posting" :can-confirm="canConfirm"
      @confirm="confirm"
  >

    <ul class="nav nav-tabs" id="add-protocol-entry-type">
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'TEXT'}" @click="entryType = 'TEXT'">
          {{ $t('_action.add_protocol_entry.type_text') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'IMAGE'}" @click="entryType = 'IMAGE'">
          {{ $t('_action.add_protocol_entry.type_image') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'FILE'}" @click="entryType = 'FILE'">
          {{ $t('_action.add_protocol_entry.type_file') }}
        </a>
      </li>
    </ul>
    <div class="tab-content p-3 border border-top-0">
      <div class="tab-pane fade" :class="{'show active': entryType === 'TEXT'}">
        <protocol-entry-text-form :template="textPost" @update="textPost = $event"/>
      </div>
    </div>
  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import ProtocolEntryTextForm from "../Form/ProtocolEntryTextForm.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";

export default {
  components: {
    ButtonWithModalConfirm,
    ProtocolEntryTextForm,
  },
  emits: ['added'],
  data() {
    return {
      textPost: null,
      entryType: 'TEXT',

      posting: false,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    root: {
      type: Object,
      required: true
    },
    authorityIri: {
      type: String,
      required: true
    },
  },
  computed: {
    canConfirm: function () {
      return this.entryType === 'TEXT' && !!this.textPost
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const basePayload = {
        constructionSite: this.constructionSite["@id"],
        root: iriToId(this.root['@id']),
        createdBy: iriToId(this.authorityIri),
        createdAt: (new Date()).toISOString()
      }

      let payload = null;
      if (this.entryType === 'TEXT') {
        payload = Object.assign({}, basePayload, this.textPost)
      }

      if (payload) {
        api.postProtocolEntry(payload, this.$t('_action.add_protocol_entry.added'))
            .then(protocolEntry => {
              this.posting = false
              this.$emit('added', protocolEntry)
            })
      }
    }
  }
}
</script>
