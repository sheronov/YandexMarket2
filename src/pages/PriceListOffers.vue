<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <div class="ml-n3 pb-5">
      <pricelist-offer-fields
          v-for="(field,key) in pricelist.offer_fields"
          :field="field"
          :tag="key"
          :values="pricelist.offer"
          :key="key"
      />
    </div>
  </div>
</template>

<script>
import PricelistOfferFields from "@/components/PricelistOfferFields";

export default {
  name: 'PriceListOffers',
  components: {PricelistOfferFields},
  props: {
    pricelist: {type: Object, required: true}
  },
  component: {
    PricelistOfferFields
  },
  data: () => ({
    nodes: [],
    fields: []
  }),
  computed: {
    rootFields() {
      return this.fields.filter(field => field.parent === null);
    }
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'offers');
    },
    addField(name, parent = null) {
      this.fields.push({
        name: name,
        parent: parent,
        rank: this.getFields(parent).length + 1
      })
    },
    saveField() {

    },
  },
  mounted() {
    this.previewXml();
  }
}
</script>