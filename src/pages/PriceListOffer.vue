<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
    <p>TODO: добавить сюда условия</p>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
      <pricelist-offer-field
          :item="pricelist.fields.find(field => field.type === offerType)"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          @field:created="fieldUpdated($event, true)"
          @field:updated="fieldUpdated"
          @field:deleted="fieldDeleted"
          @attribute:created="attributeUpdated($event, true)"
          @attribute:updated="attributeUpdated"
          @attribute:deleted="attributeDeleted"
      />
    </v-expansion-panels>
  </div>
</template>

<script>
import PricelistOfferField from "@/components/PricelistOfferField";

const {TYPE_OFFER} = require("@/components/FieldTypes");

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
    fieldUpdated(field, created = false) {
      let fields = this.pricelist.fields.slice();
      if (created) {
        fields.push(field);
      } else {
        fields.splice(fields.findIndex(item => item.id ? item.id === field.id : item.parent === field.parent), 1, field);
      }
      this.$emit('input', {fields});
    },
    fieldDeleted(field) {
      let fields = this.pricelist.fields.slice();
      fields.splice(fields.findIndex(item => item.id === field.id), 1);
      this.$emit('input', {fields: fields});
    },
    attributeUpdated(attr, created = false) {
      let attributes = this.pricelist.attributes.slice();
      if (created) {
        attributes.push(attr);
      } else {
        attributes.splice(attributes.findIndex(item => item.id ? item.id === attr.id : item.field_id === attr.field_id), 1, attr);
      }
      this.$emit('input', {attributes});
    },
    attributeDeleted(attribute) {
      let attributes = this.pricelist.attributes.slice();
      attributes.splice(attributes.findIndex(item => item.id === attribute.id), 1);
      this.$emit('input', {attributes});
    },
  },
  mounted() {
    this.previewXml();
    this.openedFields = [0];
  }
}
</script>