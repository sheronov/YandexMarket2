<template>
  <div class="yandexmarket-pricelist">
    <v-tabs class="mb-2">
      <v-tab :to="{name: 'pricelist', params: {id: id}}" ripple exact>Магазин</v-tab>
      <v-tab :to="{name: 'pricelist.categories', params: {id: id}}" ripple exact>Категории и условия</v-tab>
      <v-tab :to="{name: 'pricelist.offers', params: {id: id}}" ripple exact>Выгружаемые данные</v-tab>
      <v-spacer/>
      <v-tab :to="{name: 'pricelists'}" ripple exact>Ко всем прайс-листам</v-tab>
    </v-tabs>
    <router-view v-if="pricelist" v-bind="{pricelist}"></router-view>
    <loader :status="!pricelist"></loader>
  </div>
</template>

<script>
import Loader from "@/components/Loader";
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
        // TODO: сделать загрузку из БД
         setTimeout(() => {
           this.pricelist = {
             id: 1,
             name: 'Тестовый'
           }
         }, 1000);
      }
  },
  mounted() {
    this.id = parseInt(this.$route.params.id);
    this.loadPricelist()
  }
}
</script>