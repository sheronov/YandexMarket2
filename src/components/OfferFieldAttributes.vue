<template>
  <v-row dense class="mb-2">
    <v-col md="4" sm="6" v-for="(attribute,id) in attributes" :key="id">
      <component
          :is="attribute.type === 3 ? 'v-select' : 'v-combobox'"
          v-model="attribute.value"
          :items="attribute.properties.values"
          class="yandexmarket-offer-field-attribute"
          :placeholder="attribute.type === 3 ? 'Выберите из списка' : 'Введите значение'"
          dense
          filled
          :hint="attribute.name"
          persistent-hint
          :attach="true"
          :menu-props="{offsetY: true}"
          :label="attribute.label"
      >
<!-- append-outer-icon="icon-edit"
     click:append-outer.stop
          -->
        <template v-slot:prepend-inner>
<!--          <div class="mt-n3">-->
            <v-icon size="15" title="Атрибут" left class="ml-n1">icon-font</v-icon>
<!--          </div>-->
        </template>
      </component>
      <!--      <pre>{{ attribute }}</pre>-->
    </v-col>
  </v-row>
</template>

<script>
import VSelect from "vuetify/lib/components/VSelect/VSelect";
import VCombobox from "vuetify/lib/components/VCombobox/VCombobox";

export default {
  name: 'OfferFieldAttributes',
  components: {
    VSelect,
    VCombobox
  },
  props: {
    attributes: {required: true, type: [Array, Object]}
  },
  methods: {
    changedValue(value, attribute) {
      console.log(value, attribute);
      this.$emit('input', attribute, value);
    }
  }
}
</script>

<style>
/*.yandexmarket-offer-field-attribute .v-select__slot input,*/
/*.yandexmarket-offer-field-attribute .v-select__selection {*/
/*  text-align: center;*/
/*}*/

/*.yandexmarket-offer-field-attribute .v-select__selections {*/
/*  justify-content: center;*/
/*}*/

/*.yandexmarket-offer-field-attribute .v-select__selection + input {*/
/*  display: none;*/
/*}*/
/*.yandexmarket-offer-field-attribute .v-select__selection--comma {*/
/*  !*margin: 6px 4px 3px 0 !important;*!*/
/*}*/
</style>