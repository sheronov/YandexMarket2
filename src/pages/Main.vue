<template>
  <v-main>
    <h1 class="text-h5 mb-3 mt-2">
      YandexMarket
      <span class="subtitle-1"> - выгрузка предложений в XML для Яндекс Маркет и не только</span>
      <!--   TODO: сюда сделать breadcrumbs   -->
    </h1>
    <div class="yandexmarket-view">
    <router-view></router-view>
    </div>
  </v-main>
</template>

<script>
export default {
  name: 'Main'
}
</script>

<style>
.yandexmarket-view {
  position: relative;
}
</style>