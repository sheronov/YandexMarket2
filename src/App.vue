<template>
  <div id="yandexmarket2-app" data-vuetify>
    <v-app style="background: transparent;">
      <v-main>
        <h1 class="text-h5 mb-3 mt-2">
          YandexMarket2
          <span class="subtitle-1"> - супер компонент для выгрузки в Яндекс Маркет</span>
        </h1>
        <v-tabs class="mb-2">
          <v-tab to="/" ripple>Прайс-листы</v-tab>
          <v-tab to="/hello" ripple>Приветствие</v-tab>
          <v-tab to="/tree" ripple>Дерево категорий</v-tab>
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
#yandexmarket2-app >>> .v-application--wrap {
  min-height: auto;
}
#yandexmarket2-app >>> .v-icon.icon {
  font-family: FontAwesome;
  font-size: 16px;
  line-height: 1.5;
}
#yandexmarket2-app >>> .icon.icon-xs {
    font-size: 12px;
}
#yandexmarket2-app >>> .icon.icon-sm {
    font-size: 14px;
}
#yandexmarket2-app >>> .icon.icon-md {
    font-size: 20px;
}
#yandexmarket2-app >>> .icon.icon-lg {
    font-size: 24px;
}
</style>
