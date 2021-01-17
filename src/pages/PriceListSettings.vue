<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p>
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
    <p>
      Прайс-лист {{ pricelist }}
    </p>
  </div>
</template>

<script>

export default {
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({}),
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'settings')
    }
  },
  mounted() {
    this.previewXml();
  }
}
</script>