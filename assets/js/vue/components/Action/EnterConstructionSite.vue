<template>
  <a :href="dashboardHref" class="btn btn-primary">
    {{ $t('_action.enter_construction_site.title') }}
  </a>
</template>

<script>

export default {
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    dashboardHref: function () {
      return this.constructionSite['@id'].replace('/api', '') + '/dashboard'
    }
  }
}
</script>
