<template>
  <table class="table table-hover border">
    <tbody>
    <tr>
      <td class="w-thumbnail">
        <image-lightbox :src="constructionSite.imageUrl" :subject="constructionSite.name" />
      </td>
      <td>{{ constructionSite.name }}</td>
      <td>
        {{ getAddress(constructionSite).join(", ") }}
      </td>
      <td class="w-minimal">
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-construction-site-button :construction-site="constructionSite" />
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import TableBodyLoadingIndicator from '../Library/View/LoadingIndicatorTableBody'
import { constructionSiteFormatter } from '../../services/formatters'
import ImageLightbox from './ImageLightbox'
import EditConstructionSiteButton from '../Action/EditConstructionSiteButton'

export default {
  components: {
    EditConstructionSiteButton,
    ImageLightbox,
    TableBodyLoadingIndicator,
  },
  props: {
    constructionSite: {
      type: Object,
      required: false
    }
  },
  methods: {
    getAddress: function (constructionSite) {
      return constructionSiteFormatter.address(constructionSite)
    }
  }
}
</script>
