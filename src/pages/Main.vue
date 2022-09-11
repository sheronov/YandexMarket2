<template>
  <v-main>
    <component-header/>
    <v-alert v-if="!$xmlLoaded" type="warning" dense>
      <b>XmlWriter</b> {{ $t('class not found in the system') }}.
      <span v-html="$t('Perhaps the php module <i>libxml</i> is disabled or php is started with the flag <i>--disable-xmlwriter</i>')" />
    </v-alert>
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
.yandexmarket-view ::-webkit-scrollbar, .yandexmarket-view ::-webkit-scrollbar-thumb {
  background-color: #ffffff;
  border-radius: 0;
}
</style>
