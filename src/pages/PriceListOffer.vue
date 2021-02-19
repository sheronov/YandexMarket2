<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
      <pricelist-offer-field
          :item="pricelist.fields.find(field => field.type === offerType)"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          v-on="$listeners"
      />
    </v-expansion-panels>
  </div>
</template>

<script>
import PricelistOfferField from "@/components/PricelistField";
import {TYPE_OFFER} from "@/store/modules/field";

export default {
  name: 'PriceListOffer',
  components: {PricelistOfferField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    openedFields: [0],
    offerType: TYPE_OFFER,
  }),
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'offers');
    },
  },
  mounted() {
    this.previewXml();
    this.openedFields = [0];
  }
}
</script>