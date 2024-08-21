<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.add_issue.title')" :can-confirm="canConfirm"
      @confirm="confirm">

    <issues-form
        :construction-site="constructionSite" :craftsmen="craftsmen" :maps="maps" :template="template"
        mode="create" @update="post = $event" @confirm="$refs['modal'].confirm()">
      <template v-slot:before-description>
        <image-form @update="image = $event"/>
      </template>
    </issues-form>
  </button-with-modal-confirm>
</template>

<script>

import {api} from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import IssuesForm from "../Form/IssuesForm.vue";
import ImageForm from "../Form/ImageForm.vue";

export default {
  emits: ['added'],
  components: {
    ImageForm,
    IssuesForm,
    ButtonWithModalConfirm,
  },
  data() {
    return {
      template: {
        isMarked: false,
        wasAddedWithClient: false,
      },
      image: null,
      post: null,
      posting: false
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      default: []
    },
    craftsmen: {
      type: Array,
      default: []
    },
  },
  computed: {
    canConfirm: function () {
      return !!this.post && !!this.post?.map
    },
  },
  methods: {
    confirm: function () {
      this.posting = true
      const basic = {
        constructionSite: this.constructionSite['@id'],
        createdBy: this.constructionManagerIri,
        createdAt: (new Date()).toISOString()
      };
      const payload = Object.assign({}, this.post, basic)

      let successMessage = this.$t('_action.add_issue.added')

      if (!this.image) {
        api.postIssue(payload, successMessage)
            .then(issue => {
              this.posting = false
              this.$emit('added', issue)
            })
        return
      }

      api.postIssue(payload)
          .then(issue => {
            api.postIssueImage(issue, this.image, successMessage)
                .then(_ => {
                  this.image = null
                  this.posting = false
                  this.$emit('added', issue)
                })
          })
    }
  }
}
</script>
