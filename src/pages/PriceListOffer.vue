<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <h4>Настройка полей предложений</h4>
    <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
    <p>TODO: добавить сюда условия</p>
    <v-expansion-panels :value="openedFields" multiple class="pb-2" key="offers">
      <pricelist-offer-field
          :item="field"
          tag="offer"
          :lighten="3"
          @updated="handleUpdated"
          @created="handleUpdated"
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
    openedFields: [0]
  }),
  watch: {
    'pricelist.values.offer': {
      immediate: true,
      handler: function (offerField) {
        // TODO: это работало бы для простых объектов, нужно рекурсивно или хотя бы в строку и из строки преобразовать
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
      //TODO: implemen herel
      console.log(name, parent);
      // this.fields.push({
      //   name: name,
      //   parent: parent,
      //   rank: this.getFields(parent).length + 1
      // })
    },
    saveField() {

    },
    handleUpdated(field) {
       console.log(field);
       this.field = {
         ...this.field,
         ...field
       }
       this.previewXml();
    }
  },
  mounted() {
    this.previewXml();
  }
}
</script>