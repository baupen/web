<template>
  <tr>
    <td colspan="100">
      <looping-rhombus-spinner class="spinner-center" />
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


<style scoped>
.spinner-center {
  display: block;
  margin: 0 auto;
}
</style>
