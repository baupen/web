<template>
  <th class="clickable" @click="toggleOrder">
    <slot>

    </slot>
    <span class="ps-1"></span>
    <svg v-if="isAscOrdered" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="sort-up"
         role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
         class="svg-inline--fa fa-sort-up fa-w-10">
      <path fill="currentColor"
            d="M279 224H41c-21.4 0-32.1-25.9-17-41L143 64c9.4-9.4 24.6-9.4 33.9 0l119 119c15.2 15.1 4.5 41-16.9 41z">
      </path>
    </svg>
    <svg v-else-if="isDescOrdered" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="sort-down"
         role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
         class="svg-inline--fa fa-sort-down fa-w-10">
      <path fill="currentColor"
            d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z">
      </path>
    </svg>
    <svg v-else aria-hidden="true" focusable="false" data-prefix="fal" data-icon="sort" role="img"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-sort fa-w-10">
      <path fill="currentColor"
            d="M288 288H32c-28.4 0-42.8 34.5-22.6 54.6l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c20-20.1 5.7-54.6-22.7-54.6zM160 448L32 320h256L160 448zM32 224h256c28.4 0 42.8-34.5 22.6-54.6l-128-128c-12.5-12.5-32.8-12.5-45.3 0l-128 128C-10.7 189.5 3.6 224 32 224zM160 64l128 128H32L160 64z">
      </path>
    </svg>
  </th>
</template>

<script>
import { orderMixin } from './mixins'

export default {
  mixins: [orderMixin],
  methods: {
    toggleOrder: function () {
      // toggle states: !isActive => isAscOrdered => isDescOrdered => !isActive
      let payload = 'asc';
      if (this.isAscOrdered) {
        payload = 'desc'
      } else if (this.isDescOrdered) {
        payload = null
      }

      this.$emit('order', payload)
    }
  }
}
</script>
