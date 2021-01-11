<template>
  <v-main>
    <component-header/>
    <div class="yandexmarket-view mb-4">
      <router-view></router-view>
    </div>
  </v-main>
</template>

<script>
import ComponentHeader from "@/components/ComponentHeader";
export default {
  name: 'Main',
  components: {ComponentHeader}
}
</script>

<style scoped>
.yandexmarket-view {
  position: relative;
}
</style>