<template>
  <v-text-field
      :label="field.title"
      :value="value"
      :hint="field.help"
      @change="inputField($event)"/>
</template>

<script>
export default {
  name: 'PricelistField',
  props: {
    value: {required: true},
    field: {required: true, type: Object}
  },
  methods: {
    inputField(value) {
      this.$emit('input', value);
    }
  }
}
</script>