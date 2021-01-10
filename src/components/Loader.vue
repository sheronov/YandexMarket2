<template>
  <div class="yandexmarket-loader-overlay" v-if="status">
    <v-progress-circular indeterminate color="primary"></v-progress-circular>
  </div>
</template>

<script>
export default {
  props: {
    status: {type: Boolean}
  }
};
</script>

<style>
.yandexmarket-loader-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  min-height: 300px;
  background-color: rgba(255, 255, 255, .7);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>