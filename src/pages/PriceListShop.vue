<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Пустые поля не попадут в выгрузку.</p>
<!--    <pricelist-shop-field-->
<!--        v-for="(field,key) in sortedFields"-->
<!--        :key="field.id"-->
<!--        :field="field"-->
<!--        :marketplace="pricelist.type"-->
<!--        @input="changedValue(key,$event)"-->
<!--    />-->
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
      <pricelist-offer-field
          :item="pricelist.fields.find(field => field.type === shopType)"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          v-on="$listeners"
      />
    </v-expansion-panels>
    <p>
      TODO: Тут нужно сделать добавление новых полей и перемещение порядка
      Тут будет название, компания, урл, платформа, версия, агентство, емайл, валюта, показывать ли скидки, доставка
    </p>
  </div>
</template>

<script>

import PricelistOfferField from "@/components/PricelistOfferField";
// import PricelistShopField from "@/components/PricelistShopField";

const {TYPE_SHOP} = require("@/components/FieldTypes");

export default {
  name: 'PriceListShop',
  components: {
  //   PricelistShopField,
    PricelistOfferField
  },
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    openedFields: [0],
    shopType: TYPE_SHOP
  }),
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'shop')
    },
    // reset() {
    //   let shopField = this.pricelist.fields.find(field => field.type === TYPE_SHOP);
    //   if (shopField) {
    //     this.fields = this.pricelist.fields.filter(field => !field.is_hidden && field.parent === shopField.id).slice();
    //   }
    // },
    // changedValue(id, value) {
    //   this.$set(this.fields, id, value);
    //   this.previewXml();
    // }
  },
  mounted() {
    this.previewXml();
    this.openedFields = [0];
  }
}
</script>