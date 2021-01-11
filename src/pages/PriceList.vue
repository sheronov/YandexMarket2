<template>
  <div class="yandexmarket-pricelist">
    <v-tabs v-if="id" class="yandexmarket-pricelist-tabs" background-color="transparent" :height="40">
      <v-tab :to="{name: 'pricelist', params: {id: id}}" :ripple="false" exact>Магазин</v-tab>
      <v-tab :to="{name: 'pricelist.categories', params: {id: id}}" :ripple="false" exact>Категории и условия</v-tab>
      <v-tab :to="{name: 'pricelist.offers', params: {id: id}}" :ripple="false" exact>Выгружаемые данные</v-tab>
      <v-spacer/>
      <v-tab :to="{name: 'pricelists'}" ripple exact><v-icon left>icon icon-undo</v-icon> Ко всем прайс-листам</v-tab>
    </v-tabs>
    <v-card class="yandexmarket-pricelist-card" :loading="!pricelist">
      <v-card-text style="min-height: 300px;">
        <router-view v-if="pricelist" v-bind="{pricelist}"></router-view>
        <loader :status="!pricelist"></loader>
      </v-card-text>
    </v-card>

  </div>
</template>

<script>
import Loader from "@/components/Loader";
import api from "@/api";

export default {
  name: 'PriceList',
  components: {Loader},
  data: () => ({
    id: null,
    pricelist: null,
    type: 'yandexmarket'
  }),
  methods: {
    loadPricelist() {
      api.post('mgr/list/get', {id: this.id})
          .then(({data}) => {
            this.pricelist = data.object;
          })
    }
  },
  mounted() {
    this.id = parseInt(this.$route.params.id);
    this.loadPricelist()
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
.yandexmarket-pricelist-tabs {
  position: relative;
  margin-bottom: 0;
  z-index: 1;
}
.yandexmarket-pricelist-tabs >>> .v-tab {
  text-transform: none !important;
  letter-spacing: initial;
}
.yandexmarket-pricelist-tabs >>> .v-tab.v-tab--active {
  background-color: #fff;
  box-shadow: 0 3px 1px -2px rgba(0, 0, 0, 0.2),  0 1px 5px 0 rgba(0, 0, 0, 0.12);
}
.yandexmarket-pricelist-tabs >>> .v-tab.v-tab--active::after {
  content: '';
  display: block;
  background: #fff;
  position: absolute;
  bottom: -5px;
  left: 0;
  right: 0;
  height: 5px;
}
.yandexmarket-pricelist-tabs >>> .v-slide-group__wrapper {
  overflow: visible;
  contain: none;
}
.yandexmarket-pricelist-tabs >>> .v-tabs-slider-wrapper {
  bottom: unset;
  top: 0;
}
.yandexmarket-pricelist-card {
  border-top-left-radius: 0;
}
</style>