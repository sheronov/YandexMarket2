<template>
  <div class="yandexmarket-shop-field">
    <v-text-field
        class="mt-2 mb-3"
        :label="field.title + (field.required ? ' *': '')"
        :value="value"
        hide-details="auto"
        filled
        dense
        @change="inputField($event)"
    >
      <template v-slot:prepend>
        <v-btn icon small>
          <v-icon :color="active ? 'primary' : 'grey'" @click="active = !active">
            {{ active ? 'icon-check-square-o' : 'icon-square-o' }}
          </v-icon>
        </v-btn>
      </template>
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
    }
  }
}
</script>

<style>
.yandexmarket-shop-field {
  position: relative;
}
</style>