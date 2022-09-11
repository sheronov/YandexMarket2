<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>{{ $t('Shop main fields settings') }}</h4>
    <p class="mb-2">{{ $t('Required fields are marked with an asterisk.') }}
      {{ $t('Do not forget to save your changes.') }}</p>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers" v-if="shopField">
      <pricelist-field
          :item="shopField"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          v-on="$listeners"
          :available-fields="availableFields('shop',pricelist)"
          :available-types="availableTypes('shop',pricelist)"
      />
    </v-expansion-panels>
    <p>{{ $t('Empty fields will not be included in the file.') }}
      {{ $t('The save changes button appears after you start editing.') }}</p>
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
  }),
  computed: {
    ...mapGetters('marketplace', ['availableFields']),
    ...mapGetters('field', ['availableTypes']),
    shopField() {
      return this.pricelist.fields.find(field => field.type === 2);
    }
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