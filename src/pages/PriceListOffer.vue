<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <v-row class="ma-0">
      <v-flex>
        <h4>Настройка полей предложений</h4>
        <p class="mb-2">Интерактивный режим добавления и редактирования полей</p>
      </v-flex>
      <v-spacer/>
      <v-btn @click="previewXml" icon title="Поменять предложение в предпросмотре">
        <v-icon>icon-refresh</v-icon>
      </v-btn>
    </v-row>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
      <pricelist-offer-field
          :item="offerField"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          :available-fields="availableFields('offer',pricelist)"
          :available-types="availableTypes('offer',pricelist)"
          v-on="$listeners"
      />
    </v-expansion-panels>
  </div>
</template>

<script>
import PricelistOfferField from "@/components/PricelistField";
import {mapGetters} from "vuex";

export default {
  name: 'PriceListOffer',
  components: {PricelistOfferField},
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    openedFields: [0],
  }),
  computed: {
    ...mapGetters('marketplace', ['availableFields']),
    ...mapGetters('field', ['availableTypes']),
    offerField() {
      return this.pricelist.fields.find(field => field.type === 6);
    }
  },
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