<template>
  <div id="yandexmarket-app" data-vuetify>
    <v-app style="background: transparent;">
      <router-view></router-view>
    </v-app>
  </div>
</template>

<script>
export default {
  name: 'yandexmarket-app',
  created() {
    this.$store.dispatch('loadClassKeys');
    this.$store.dispatch('marketplace/loadList');
  }
};
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
#yandexmarket-app {
  padding: 0 15px;
}

#yandexmarket-app >>> .v-application--wrap {
  min-height: auto;
}

#yandexmarket-app >>> .v-icon.icon {
  /*noinspection CssNoGenericFontName*/
  font-family: FontAwesome;
  font-size: 16px;
  line-height: 1.5;
}

#yandexmarket-app >>> .icon.icon-xs {
  font-size: 12px;
}

#yandexmarket-app >>> .icon.icon-sm {
  font-size: 14px;
}

#yandexmarket-app >>> .icon.icon-md {
  font-size: 20px;
}

#yandexmarket-app >>> .icon.icon-lg {
  font-size: 24px;
}
</style>
