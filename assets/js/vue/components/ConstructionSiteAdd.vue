<template>
  <form ref="form" @submit.prevent="">
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" class="form-control" id="name" v-model="constructionSite.name" required>
    </div>

    <div class="form-group">
      <label for="streetAddress">StreetAddress</label>
      <input type="text" class="form-control" id="streetAddress" v-model="constructionSite.streetAddress" required>
    </div>

    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="postalCode">PostalCode *</label>
        <input type="number" class="form-control" id="postalCode" v-model.number="constructionSite.postalCode" required>
      </div>
      <div class="form-group col-md-8">
        <label for="locality">Locality *</label>
        <input type="text" class="form-control" id="locality" v-model="constructionSite.locality" required>
      </div>
    </div>

    <button type="submit" @click="save" class="btn btn-primary">{{ $t("switch.actions.create_construction_site") }}</button>
  </form>
</template>

<script>

export default {
  emits: ['save'],
  data() {
    return {
      constructionSite: {}
    }
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    }
  },
  methods: {
    save: function () {
      if (this.$refs.form.reportValidity()) {
        this.$emit('save', this.constructionSite)
      }
    }
  }
}
</script>
