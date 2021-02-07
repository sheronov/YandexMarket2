<template>
  <div class="yandexmarket-shop-value mb-3">
    <!-- TODO: сделать поддержку разных компонентов -->
    <v-select
        v-if="field.type === 4"
        :label="field.label"
        :value="field.value"
        :items="field.properties.values"
        filled
        dense
        multiple
        chips
        deletable-chips
        small-chips
        hide-details="auto"
        item-value="value"
        item-text="text"
        @change="inputField($event)"
        :menu-props="{offsetY: true}"
        :attach="true"
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
          <strong>{{ item.value }}</strong>
          <span class="pl-1">({{ item.text }})</span>
        </v-chip>
      </template>
    </v-select>
    <v-checkbox
        v-else-if="field.type === 13"
        :label="field.label"
        :input-value="!!field.value"
        @change="inputField(!!$event)"
    ></v-checkbox>
    <v-text-field
        v-else
        :label="field.label"
        :value="field.value"
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
  name: 'PricelistShopField',
  props: {
    field: {required: true},
    marketplace: {required: true},
  },
  computed: {
    label() {
      let label = this.field.name;
      let lexicon = `ym_${this.marketplace}_shop_${this.field.name}`;
      if (lexicon !== this.$t(lexicon)) {
        label = this.$t(lexicon);
      } else {
        lexicon = `ym_${this.marketplace}_${this.field.name}`;
        if (lexicon !== this.$t(lexicon)) {
          label = this.$t(lexicon);
        }
      }

      if (this.field.properties && this.field.properties.required) {
        label += ' *';
      }
      return label;
    },
    help() {
      return '';
    }
  },
  methods: {
    inputField(value) {
      this.$emit('input', {...this.field, value: value});
    },
    removeChip(item) {
      let values = [...this.field.value];
      values.splice(values.indexOf(item.key), 1)
      this.inputField(values);
    },
    makeFirst(item) {
      let values = [...this.field.value];
      values.splice(values.indexOf(item.key), 1);
      values.unshift(item.key);
      this.inputField(values);
    }
  }
}
</script>

<style>
.yandexmarket-shop-value {
  position: relative;
}
</style>