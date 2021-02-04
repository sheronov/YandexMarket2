<template>
  <v-row>
    <v-col v-for="(attribute,id) in attributes" :key="id">
      <pre>{{attribute}}</pre>
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: 'OfferFieldAttributes',
  props: {
    attributes: {required: true, type: [Array, Object]}
  }
}
</script>