<template>
  <v-main>
    <component-header/>
    <v-alert v-if="!$xmlLoaded" type="warning" dense>
      Не найден класс <b>XmlWriter</b>. Возможно, отключён php модуль <i>libxml</i> или php запущен с флагом <i>--disable-xmlwriter</i>.
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
</style>