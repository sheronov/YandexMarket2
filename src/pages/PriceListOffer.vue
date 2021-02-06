<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
    <v-expansion-panels v-model="opened" multiple class="pb-2">
      <pricelist-offer-field
          :field="field"
          tag="offer"
          :lighten="3"
      />
    </v-expansion-panels>
  </div>
</template>

<script>
import PricelistOfferField from "@/components/PricelistOfferField";

export default {
  name: 'PriceListOffer',
  components: {PricelistOfferField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    nodes: [],
    field: {},
    opened: [0],
  }),
  watch: {
    'pricelist.values.offer': {
      immediate: true,
      handler: function (offerField) {
        this.field = {
          ...this.field,
          ...offerField
        }
      }
    }
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'offers');
    },
    addField(name, parent = null) {
      console.log(name, parent);
      // this.fields.push({
      //   name: name,
      //   parent: parent,
      //   rank: this.getFields(parent).length + 1
      // })
    },
    saveField() {

    },
  },
  mounted() {
    this.previewXml();
  }
}
</script>