<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <div class="yandexmarket-shop-field" v-for="field in fields" :key="field.key">
      <pricelist-field :field="field" :value="form[field.key]" @input="changedValue(field, $event)"/>
    </div>
    <p>
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
    <p>
      Прайс-лист {{ pricelist }}
    </p>
  </div>
</template>

<script>

import PricelistField from "@/components/PricelistField";

export default {
  name: 'PricelistSettings',
  components: {PricelistField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    fields: [],
    currencies: [],
    form: {}
  }),
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'shop', this.form)
    },
    save() {
      this.$emit('@input:settings', this.form);
    },
    reset() {
      this.form = {...this.pricelist.shop || {}};
    },
    changedValue(field, value) {
      console.log(field, value);
    }
  },
  mounted() {
    this.fields = this.pricelist.fields || [];
    this.reset();
    this.previewXml();
  }
}
</script>