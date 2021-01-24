<template>
  <div class="yandexmarket-offer-field pl-3">
    Поле {{ tag }}. Значение {{ value }}
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
  computed: {
    value() {
      let value = this.values[this.tag];
      if (Array.isArray(value)) {
        value = null;
        // значит параметр множественный, скорее всего param
      } else if (value instanceof Object) {
        //значит есть атрибуты
        value = value.column || value.handler || null;
      }
      return value;
    }
  },
  methods: {
    addField() {

    },
    addAttribute() {

    }
  }
}
</script>

<style>
  .yandexmarket-offer-field {
    position: relative;
  }
</style>