<template>
  <h2 class="mt-5">{{ $t('map._plural') }}</h2>
  <p>{{ $t('edit.maps_help') }}</p>
  <div class="btn-group">
    <add-map-button :construction-site="constructionSite" :maps="notDeletedMaps" @added="maps.push($event)" />
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
  computed: {
    notDeletedMaps: function () {
      if (!this.maps) {
        return null;
      }

      return this.maps.filter(c => !c.isDeleted)
    }
  },
  mounted () {
    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)
  }
}
</script>
