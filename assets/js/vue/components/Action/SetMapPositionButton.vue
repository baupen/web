<template>
  <button-with-modal-confirm
      :title="$t('_action.select_map_position.title')"
      :can-confirm="!!position"
      @confirm="confirm"
      :can-abort="true"
      :abort-title="$t('_action.select_map_position.no_position')"
      @abort="abort"
      modal-size="lg">

    <set-map-position-form :current-position="currentPosition" :construction-site="constructionSite" :map="map" @update="position = $event" />
  </button-with-modal-confirm>
</template>

<script>
import {api} from "../../services/api";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";
import SetMapPositionForm from "../Form/SetMapPositionForm.vue";

export default {
  emits: ['selected'],
  components: {
    SetMapPositionForm,
    ButtonWithModalConfirm,
  },
  data() {
    return {
      position: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    currentPosition: {
      type: Object,
      required: false,
    },
  },
  computed: {
    src: function () {
      if (!this.map.fileUrl) {
        return null
      }

      return api.getIssuesRenderLink(this.constructionSite, this.map, {number: -1})
    },
  },
  methods: {
    abort: function () {
      this.$emit('selected', null)
    },
    confirm: function (){
      this.$emit('selected', this.position)
    }
  },
}
</script>
