<template>
    <div class="clickable" @click.prevent.stop="toggle">
        <font-awesome-icon v-if="issue.wasAddedWithClient" :icon="['fas', 'user-check']"/>
        <font-awesome-icon v-else :icon="['fal', 'user-check']"/>
    </div>
</template>


<script>
  export default {
    props: {
      issue: {
        type: Object,
        required: true
      }
    },
    methods: {
      toggle: function () {
        this.$emit('edit-started');
        this.issue.wasAddedWithClient = !this.issue.wasAddedWithClient;
        this.$emit('edit-confirm');
      }
    }
  }

</script>
