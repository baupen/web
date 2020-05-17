<template>
    <div class="clickable" @click.prevent.stop="toggle">
        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
        <font-awesome-icon v-else :icon="['fal', 'star']"/>
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
        this.issue.isMarked = !this.issue.isMarked;
        this.$emit('edit-confirm');
      }
    }
  }

</script>
