<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Настройки основных полей магазина</h4>
    <p class="mb-2">Обязательные поля отмечены звёздочкой. Не забывайте сохранять изменения.</p>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
      <pricelist-field
          :item="pricelist.fields.find(field => field.type === shopType)"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          v-on="$listeners"
          :available-fields="availableFields('shop',pricelist)"
          :available-types="availableTypes('shop',pricelist)"
      />
    </v-expansion-panels>
    <p>Пустые поля не попадут в выгрузку. Сохранение каждого поля появляется после изменений.</p>
  </div>
</template>

<script>

import PricelistField from "@/components/PricelistField";
import {mapGetters} from 'vuex';

export default {
  name: 'PriceListShop',
  components: {
    PricelistField
  },
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    openedFields: [0],
    shopType: 2
  }),
  computed: {
    ...mapGetters('marketplace', ['availableFields']),
    ...mapGetters('field', ['availableTypes']),
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'shop')
    },
  },
  mounted() {
    this.previewXml();
    this.openedFields = [0];
  }
}
</script>