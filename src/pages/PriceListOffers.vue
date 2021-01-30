<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
    <!--  TODO: первичные foreach тоже перенести в pricelist-offer-fields  -->
    <div class="pb-2">
      <pricelist-offer-fields
          :fields="pricelist.offer_fields"
          :values="pricelist.offer"
      >
      </pricelist-offer-fields>
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
    fields: [],
    opened: [],
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
    this.opened = Object.keys(this.pricelist.offer_fields).map((field, index) => index);
  }
}
</script>