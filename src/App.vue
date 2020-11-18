<template>
  <div id="yandexmarket2-app" data-vuetify>
    <v-app style="background: transparent;">
      <v-main>
        <h1 class="text-h5 mb-3 mt-2">
          YandexMarket2
          <span class="subtitle-1"> - супер компонент для выгрузки в яндекс маркет</span>
        </h1>
        <v-tabs class="mb-2" grow>
          <v-tab to="/">Прайс-листы</v-tab>
          <v-tab to="/hello">Приветствие</v-tab>
          <v-tab to="/tree">Дерево категорий</v-tab>
        </v-tabs>
        <router-view></router-view>
      </v-main>
    </v-app>
  </div>
</template>

<script>
export default {
  name: 'yandexmarket2-app'
};
</script>

<style scoped>
#yandexmarket2-app {
  padding: 0 20px;
}
</style>
