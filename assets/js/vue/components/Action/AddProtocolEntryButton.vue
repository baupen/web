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
        <protocol-entry-text-form @update="textPost = $event" :text-mode="true"/>
      </div>
      <div class="tab-pane fade" :class="{'show active': entryType === 'IMAGE'}">
        <image-form @update="image = $event" />
        <protocol-entry-text-form @update="imagePost = $event" :text-mode="false"/>
      </div>
      <div class="tab-pane fade" :class="{'show active': entryType === 'FILE'}">
        <file-form @update="file = $event" />
        <protocol-entry-text-form @update="filePost = $event" :text-mode="false"/>
      </div>
    </div>
  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import ProtocolEntryTextForm from "../Form/ProtocolEntryTextForm.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";
import MapForm from "../Form/MapForm.vue";
import FileForm from "../Form/FileForm.vue";
import ImageForm from "../Form/ImageForm.vue";

export default {
  components: {
    ImageForm,
    FileForm, MapForm,
    ButtonWithModalConfirm,
    ProtocolEntryTextForm,
  },
  emits: ['added'],
  data() {
    return {
      entryType: 'TEXT',

      textPost: null,
      imagePost: null,
      filePost: null,

      image: null,
      file: null,

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
      return (this.entryType === 'TEXT' && !!this.textPost) ||
          (this.entryType === 'IMAGE' && !!this.imagePost && !!this.image) ||
          (this.entryType === 'FILE' && !!this.filePost && !!this.file)
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      let payload = Object.assign({
        constructionSite: this.constructionSite["@id"],
        root: iriToId(this.root['@id']),
        type: this.entryType,
        createdBy: iriToId(this.authorityIri),
      })

      // add meta
      switch (this.entryType) {
        case "TEXT":
          payload = {...payload, ...this.textPost}
          break;
        case "IMAGE":
          payload = {...payload, ...this.imagePost}
          break;
        case "FILE":
          payload = {...payload, ...this.filePost}
          break;
      }

      const successMessage = this.$t('_action.add_protocol_entry.added');
      if (!this.file && !this.image) {
        api.postProtocolEntry(payload, successMessage)
            .then(protocolEntry => {
              this.posting = false
              this.$emit('added', protocolEntry)
            })
        return
      }

      api.postProtocolEntry(payload)
          .then(protocolEntry => {
            const file = this.entryType === 'IMAGE' ? this.image : this.file;
            api.postProtocolEntryFile(protocolEntry, file, successMessage)
                .then(_ => {
                  this.file = null
                  this.posting = false
                  this.$emit('added', protocolEntry)
                })
          })
    }
  }
}
</script>
