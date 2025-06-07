<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.add_issue.title')" :can-confirm="canConfirm"
      @confirm="confirm" :repeat-confirm-label="$t('_action.add_issue.add_more')">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'plus']" class="pe-1" />
      {{ $t('_action.add_issue.title') }}
    </template>

    <issues-form
        ref="issue-form"
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
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";

export default {
  emits: ['added'],
  components: {
    CustomCheckbox,
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

      const finishedPosting = (issue) => {
        this.posting = false
        this.$emit('added', issue)

        this.$refs['issue-form'].position = undefined
      }

      if (!this.image) {
        api.postIssue(payload, successMessage)
            .then(issue => finishedPosting(issue))
        return
      }

      api.postIssue(payload)
          .then(issue => {
            api.postIssueImage(issue, this.image, successMessage)
                .then(_ => finishedPosting(issue))
          })
    }
  }
}
</script>
