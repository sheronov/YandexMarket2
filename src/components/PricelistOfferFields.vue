<template>
  <div class="yandexmarket-offer-field">
    Поле {{ tag }}
    <div v-if="field.attributes">
      Атрибуты:
      <pre>{{ field.attributes }}</pre>
    </div>
    <div v-if="field.fields" class="yandexmarket-offer-field-children">
      <pricelist-offer-fields
          v-for="(child,childTag) in field.fields"
          :key="childTag"
          :field="child"
          :tag="childTag"
          :values="values"/>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PricelistOfferFields',
  props: {
    values: {required: true, type: [Object, Array]},
    field: {required: true, type: Object},
    tag: {required: true, type: String}
  },
  methods: {
    addField() {

    },
    addAttribute() {

    }
  }
}
</script>