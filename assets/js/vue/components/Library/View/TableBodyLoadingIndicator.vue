<template>
  <tr>
    <td colspan="100">
      <looping-rhombus-spinner
          class="spinner-center"
          :animation-duration="2000"
          :size="40"
          color="#343477" />
    </td>
  </tr>
</template>

<script>
import LoopingRhombusSpinner from './Base/LoopingRhombusSpinner'

export default {
  components: {
    LoopingRhombusSpinner
  }
}
</script>


<style scoped="true">
  .spinner-center {
    display: block;
    margin: 0 auto;
  }
</style>
