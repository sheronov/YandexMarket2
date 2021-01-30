<template>
  <v-expansion-panels v-model="opened" multiple accordion>
    <pricelist-offer-field
        v-for="(field,key) in fields"
        :key="key"
        :tag="key"
        :field="field"
        :values="values"
    >
    </pricelist-offer-field>
  </v-expansion-panels>
</template>

<script>

export default {
  name: 'PricelistOfferFields',
  components: {PricelistOfferField: () => import('@/components/PricelistOfferField')},
  props: {
    values: {required: true, type: [Object, Array]},
    fields: {required: true, type: [Object, Array]},
  },
  data: () => ({
    opened: [],
  }),
  mounted() {
    this.opened = Object.keys(this.fields).map((field, index) => index);
  }
}
</script>