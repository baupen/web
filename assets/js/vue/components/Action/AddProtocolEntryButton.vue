<template>
  <button-with-modal-confirm
      :title="$t('_action.add_protocol_entry.title')"
      :button-disabled="posting" :can-confirm="canConfirm"
      @confirm="confirm"
  >

    <div class="mb-3">
      <custom-radio-field
          for-id="protocol-entry-type-text"
          :label="$t('_action.add_protocol_entry.type_text')">
        <input
            id="protocol-entry-type-text" class="form-check-input" type="radio"
            name="protocol-entry-type" value="TEXT"
            v-model="entryType">
      </custom-radio-field>

      <custom-radio-field
          for-id="protocol-entry-type-image"
          :label="$t('_action.add_protocol_entry.type_image')">
        <input
            id="protocol-entry-type-image" class="form-check-input" type="radio"
            name="protocol-entry-type" value="IMAGE"
            v-model="entryType">
      </custom-radio-field>

      <custom-radio-field
          for-id="protocol-entry-type-file"
          :label="$t('_action.add_protocol_entry.type_file')">
        <input
            id="protocol-entry-type-file" class="form-check-input" type="radio"
            name="protocol-entry-type" value="FILE"
            v-model="entryType">
      </custom-radio-field>
    </div>

    <template v-if="entryType === 'TEXT'">
      <protocol-entry-text-form @update="textPost = $event" :text-mode="true"/>
    </template>

    <template v-if="entryType === 'IMAGE'">
      <image-form @update="image = $event"/>
      <protocol-entry-text-form @update="imagePost = $event" :text-mode="false"/>
    </template>
    <template v-if="entryType === 'FILE'">
      <file-form @update="file = $event"/>
      <protocol-entry-text-form @update="filePost = $event" :text-mode="false"/>
    </template>

  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import ProtocolEntryTextForm from "../Form/ProtocolEntryTextForm.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";
import MapForm from "../Form/MapForm.vue";
import FileForm from "../Form/FileForm.vue";
import ImageForm from "../Form/ImageForm.vue";
import CustomRadioField from "../Library/FormLayout/CustomRadioField.vue";

export default {
  components: {
    CustomRadioField,
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
      if (this.entryType === 'TEXT') {
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
