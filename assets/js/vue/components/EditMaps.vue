<template>
  <h2 class="mt-5">{{ $t('map._plural') }}</h2>
  <p>{{ $t('edit.maps_help') }}</p>
  <div class="btn-group">
    <add-map-button :construction-site="constructionSite" @added="maps.push($event)" />
    <!-- <import-maps-button :construction-site="constructionSite" :maps="maps" @imported="reload" /> -->
  </div>
  <maps-edit-table class="mt-2" :construction-site="constructionSite" :maps="notDeletedMaps" />
</template>

<script>
import AddMapButton from './Action/AddMapButton'
import MapsEditTable from './View/MapsEditTable'
import { api } from '../services/api'

export default {
  components: {
    MapsEditTable,
    AddMapButton
  },
  data() {
    return {
      maps: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    reload: function () {
      this.maps = null;

      api.getMaps(this.constructionSite)
          .then(maps => this.maps = maps)
    }
  },
  computed: {
    notDeletedMaps: function () {
      if (!this.maps) {
        return null;
      }

      return this.maps.filter(c => !c.isDeleted)
    }
  },
  mounted () {
    this.reload()
  }
}
</script>
