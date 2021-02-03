<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Пустые поля не попадут в выгрузку.</p>

    <pricelist-shop-value
        v-for="(value,key) in values"
        :key="key"
        :value="value"
        :id="key"
        :marketplace="pricelist.type"
        @input="changedValue(key,$event)"
    />
    <p>
      Тут нужно сделать добавление новых полей и перемещение порядка
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
  </div>
</template>

<script>

// import PricelistField from "@/components/PricelistField";
import PricelistShopValue from "@/components/PricelistShopValue";

export default {
  name: 'PriceListShop',
  components: {PricelistShopValue},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    fields: [],
    currencies: [],
    form: {},
    values: {}
  }),
  watch: {
    'pricelist.values.shop': {
      immediate: true,
      handler: function (pricelistValues) {
        this.values = {
          ...this.values,
          ...pricelistValues
        }
      }
    }
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'shop', this.values)
    },
    save() {
      this.$emit('@input:settings', this.form);
    },
    reset() {
      this.form = {...this.pricelist.shop || {}};
    },
    changedValue(id, value) {
      // console.log(id, value);
      this.$set(this.values, id, value);
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