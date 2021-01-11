<template>
  <h2 class="mb-5 mt-5 yandexmarket-component-name">
    YandexMarket
    <v-breadcrumbs v-if="items.length > 1" class="yandexmarket-breadcrumbs" :items="items"/>
    <span class="subtitle-1" v-else> &nbsp;-&nbsp; выгрузка предложений в XML для Яндекс Маркет и не только</span>
  </h2>
</template>

<script>
export default {
  name: 'ComponentHeader',
  data: () => ({
    items: []
  }),
  methods: {
    updateBreadcrumbs() {
      const params = Object.assign({}, this.$route.params);
      console.log(this.$route, JSON.stringify(this.$route.params), params);
      this.items = this.$route.matched
          .filter(item => item.meta && item.meta.title && (item.name || item.meta.to))
          .map(item => {
            return {
              text: item.meta.title,
              to: {name: item.name || item.meta.to, params: params},
              exact: true,
              disabled: false
            };
          })
    }
  },
  watch: {
    '$route'() {
      this.updateBreadcrumbs();
    }
  },
  mounted() {
    this.updateBreadcrumbs();
  }
}
</script>

<style scoped>
.yandexmarket-breadcrumbs {
  display: inline-block;
  padding-top: 0;
  padding-bottom: 0;
  top: -2px;
  position: relative;
}

.yandexmarket-breadcrumbs::before {
  content: '/';
  font-size: 18px;
  color: rgba(0, 0, 0, .3);
  position: relative;
  left: -12px;
  top: 2px;
}
</style>