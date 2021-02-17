<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Пустые поля не попадут в выгрузку.</p>
    <pricelist-shop-field
        v-for="(field,key) in sortedFields"
        :key="field.id"
        :field="field"
        :marketplace="pricelist.type"
        @input="changedValue(key,$event)"
    />
    <p>
      TODO: Тут нужно сделать добавление новых полей и перемещение порядка
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
  </div>
</template>

<script>

// import PricelistField from "@/components/PricelistField";
import PricelistShopField from "@/components/PricelistShopField";

const {TYPE_SHOP} = require("@/components/FieldTypes");

export default {
  name: 'PriceListShop',
  components: {PricelistShopField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    fields: [],
  }),
  computed: {
    sortedFields() {
      return this.fields.slice().sort((f1, f2) => f1.rank - f2.rank);
    }
  },
  watch: {
    'pricelist.fields': {
      immediate: true,
      handler: function (fields) {
        let shopField = fields.find(field => field.type === TYPE_SHOP);
        if (shopField) {
          this.fields = fields.filter(field => !field.is_hidden && field.parent === shopField.id).slice();
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
      let shopField = this.pricelist.fields.find(field => field.type === TYPE_SHOP);
      if (shopField) {
        this.fields = this.pricelist.fields.filter(field => !field.is_hidden && field.parent === shopField.id).slice();
      }
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