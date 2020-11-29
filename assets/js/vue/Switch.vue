<template>
  <div id="switch">
    <p>Hi</p>
    <p>{{ $t("switch.mine") }}</p>
    <spinner :spin="true" />
  </div>
</template>

<script>

export default {
  data () {
    return {
      isLoading: true
    }
  }
}

</script>
