<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Пустые поля не попадут в выгрузку.</p>

    <pricelist-shop-field
        v-for="(field,key) in fields"
        :key="key"
        :field="field"
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
import PricelistShopField from "@/components/PricelistShopField";

export default {
  name: 'PriceListShop',
  components: {PricelistShopField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    fields: {},
  }),
  watch: {
    'pricelist.values.shop': {
      immediate: true,
      handler: function (shopFields) {
        this.fields = {
          ...this.fields,
          ...shopFields
        }
      }
    }
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'shop', this.fields)
    },
    save() {
      this.$emit('@input:shop', this.fields);
    },
    reset() {
      this.fields = {...this.pricelist.values.shop};
    },
    changedValue(id, value) {
      this.$set(this.fields, id, value);
      this.previewXml();
    }
  },
  mounted() {
    this.reset();
    this.previewXml();
  }
}
</script>