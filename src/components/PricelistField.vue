<template>
  <div class="yandexmarket-shop-field mb-3">
    <!-- TODO: сделать поддержку разных компонентов -->
    <v-select
        v-if="field.component === 'select'"
        :label="field.title + (field.required ? ' *': '')"
        :value="value"
        :items="field.values"
        filled
        dense
        multiple
        chips
        deletable-chips
        small-chips
        hide-details="auto"
        item-value="key"
        @change="inputField($event)"
    >
      <template v-slot:selection="{ attrs, index, item, selected }">
        <v-chip
            v-bind="attrs"
            :input-value="selected"
            text-color="white"
            :color="!index ? 'primary' : 'grey'"
            :title="!index ? 'Основная валюта' : 'Выбрать основной'"
            @click.stop="makeFirst(item)"
            @click:close="removeChip(item)"
            close
            small
        >
          <strong>{{ item.key }}</strong>
          <span class="pl-1">({{ item.text }})</span>
        </v-chip>
      </template>
    </v-select>
    <v-text-field
        v-else
        :label="field.title + (field.required ? ' *': '')"
        :value="value"
        hide-details="auto"
        filled
        dense
        @change="inputField($event)"
    >
      <template v-slot:append v-if="field.help">
        <v-tooltip bottom :max-width="400" :close-delay="500">
          <template v-slot:activator="{ on }">
            <v-icon v-on="on">
              icon-question-circle
            </v-icon>
          </template>
          <div class="text-caption" style="white-space: pre-line;">{{ field.help }}</div>
        </v-tooltip>
      </template>
    </v-text-field>
  </div>
</template>

<script>
export default {
  name: 'PricelistField',
  props: {
    value: {required: true},
    field: {required: true, type: Object}
  },
  data: () => ({
    active: true
  }),
  methods: {
    inputField(value) {
      this.$emit('input', value);
    },
    removeChip(item) {
      let values = [...this.value];
      values.splice(values.indexOf(item.key), 1)
      this.inputField(values);
    },
    makeFirst(item) {
      let values = [...this.value];
      values.splice(values.indexOf(item.key), 1);
      values.unshift(item.key);
      this.inputField(values);
    }
  }
}
</script>

<style>
.yandexmarket-shop-field {
  position: relative;
}
</style>