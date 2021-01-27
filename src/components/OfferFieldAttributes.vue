<template>
  <v-row>
    <v-col v-for="(attribute,param) in attributes" :key="param">
        {{param}}="{{attribute.title}}"
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