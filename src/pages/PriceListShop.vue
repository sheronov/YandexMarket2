<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Пустые поля не попадут в выгрузку.</p>
    <pricelist-field
        v-for="field in fields"
        :key="field.key"
        :field="field"
        :value="form[field.key]"
        @input="changedValue(field, $event)"
    />
    <p>
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
  </div>
</template>

<script>

import PricelistField from "@/components/PricelistField";

export default {
  name: 'PriceListShop',
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
      this.$set(this.form, field.key, value);
      this.previewXml();
    }
  },
  mounted() {
    this.fields = this.pricelist.fields || [];
    this.reset();
    this.previewXml();
  }
}
</script>