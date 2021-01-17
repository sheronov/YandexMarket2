<template>
  <div class="yandexmarket-pricelist-columns">
    <h4>Настройка колонок </h4>
  </div>
</template>

<script>
export default {
  name: 'PriceListColumns',
  props: {
    pricelist: {type: Object, required: true}
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'offer');
    }
  },
  mounted() {
    this.previewXml();
  }
}
</script>