<template>
  <div class="yandexmarket-pricelist-conditions mb-n5">
    <pricelist-condition
        v-for="condition in conditions"
        :key="condition.id"
        :item="condition"
        v-on="$listeners"
    />
    <div class="text-right">
      <v-btn small class="mt-3 mb-0" color="grey lighten-4" @click="addCondition"
             :disabled="!!conditions.filter(c => !c.id).length">
        <v-icon class="icon-sm" left>icon-plus</v-icon>
        <template v-if="conditions.filter(c => !c.id).length">
          Сохраните новое условие
        </template>
        <template v-else>
          Добавить условие
        </template>
      </v-btn>
    </div>
  </div>
</template>

<script>

import PricelistCondition from "@/components/PricelistCondition";

export default {
  name: 'PricelistConditions',
  components: {PricelistCondition},
  props: {
    conditions: {required: true},
    pricelist: {required: true},
  },
  methods: {
    addCondition() {
      this.$emit('condition:created', {
        id: null,
        pricelist_id: this.pricelist.id,
        column: null,
        operator: null,
        value: null
      });
    },
  }
}
</script>