<template>
  <div class="yandexmarket-field-value">
    <v-card-title class="pa-0 flex-nowrap">
      <template v-if="isCurrencies(field)">
        <v-select
            :value="field.value"
            :items="field.values"
            filled
            dense
            multiple
            solo
            chips
            deletable-chips
            prepend-inner-icon="icon-tags"
            small-chips
            hide-details="auto"
            item-value="value"
            item-text="text"
            @change="changedValue"
            :menu-props="{offsetY: true}"
            :attach="true"
        >
          <template v-slot:selection="{ attrs, index, item, selected }">
            <v-chip
                v-bind="attrs"
                :input-value="selected"
                text-color="white"
                :color="!index ? 'primary' : 'grey'"
                :title="$t(!index ? 'Main currency' : 'Make main')"
                @click.stop="makeFirst(item)"
                @click:close="removeChip(item)"
                close
                small
            >
              <strong v-if="item.value">{{ item.value }}</strong>
              <span class="pl-1" v-if="item.text">({{ item.text }})</span>
              <span v-else>{{ item }}</span>
            </v-chip>
          </template>
        </v-select>
      </template>
      <template v-else-if="isSimpleString(field)">
        <v-text-field
            :value="field.value"
            @input="changedValue"
            :label="$t('Enter value')"
            :placeholder="$t('Will get into XML without processing and replacing placeholders')"
            hide-details
            solo
            dense
        >
          <template v-slot:prepend-inner>
            <div class="text-no-wrap mr-2">
              <v-icon left color="inherit">icon-paragraph</v-icon>
              <code style="position:relative; top:1px;">{{ $t('Text') }}</code>
            </div>
          </template>
        </v-text-field>
      </template>
      <template v-else-if="isRawXml(field)">
        <div class="text-caption">
          {{ $t('The result will not be wrapped. Make sure you are generating valid XML.') }}
          {{ $t('This is the standard code handler where the corresponding fields are available.') }}
        </div>
      </template>
      <template v-else>
        <v-combobox
            :value="value"
            @input="changedValue"
            :filter="valueSearch"
            :items="classKeys.filter(ck => !ck.skipped)"
            :attach="true"
            :label="$t('Select object field')"
            :placeholder="$t('or type in Class.key format and press Enter')"
            item-value="value"
            item-text="text"
            hide-details
            solo
            dense
            clearable
        >
          <template v-slot:prepend-inner>
            <div class="text-no-wrap mr-2">
              <v-icon left color="inherit">icon-list-ul</v-icon>
              <code style="position:relative; top:1px;">{{ valuePrepend }}</code>
            </div>
          </template>
          <template v-slot:item="{item}">
            <code>{{ item.value }}</code>
            <span class="pl-1">{{ item.text }}</span>
            <small v-if="item.help" class="pl-1 grey--text">&nbsp;({{ item.help }})</small>
          </template>
        </v-combobox>
        <v-text-field
            v-if="isPicture(field)"
            :value="field.properties.count"
            @input="countChanged"
            type="number"
            class="ml-2"
            :title="$t('Number of images for each product (10 maximum)')"
            min="0"
            max="10"
            :label="$t('Count')"
            style="max-width: 100px"
            solo
            dense
            hide-details
        >
        </v-text-field>
        <v-btn v-else
               :title="$t(openedCode ? 'To close, clear the entered code' : 'Add value handler code')"
               :color="openedCode ? 'secondary' : 'accent'"
               @click="toggleCode"
               class="ml-3"
               min-width="30"
               elevation="0">
          <v-icon>icon-code</v-icon>
        </v-btn>
      </template>
    </v-card-title>
    <v-sheet elevation="1" v-if="openedCode" class="mt-2" style="position: relative;">
      <vue-codemirror
          v-if="rerender"
          v-model="field.handler"
          :options="cmOptions"
          :placeholder="`${$t('Example')}: {$input === '${$t('Yes')}' ? true : false}`"
      ></vue-codemirror>
      <v-tooltip left :max-width="450" :close-delay="200" :attach="true">
        <template v-slot:activator="{on}">
          <v-btn v-on="on" x-small fab icon absolute style="right: -4px; top: -4px;">
            <v-icon color="grey darken-1">icon-question-circle</v-icon>
          </v-btn>
        </template>
        <div class="text-caption" style="white-space: pre-line;">
          {{ $t('INLINE processing of the field on Fenom (the value goes to $input)') }}<br>
          {{ $t('Needed to cast boolean, cut out unnecessary tags/texts, process arrays, TV fields, or for independent values.') }}
          <br><br>
          {{ $t('Available resource fields {$resource.pagetitle}, miniShop2 products {$data.price}, ms2 options {$option.color}, TV fields {$tv.tag}.') }}<br>
          {{ $t('All required TV fields, options will be joined automatically') }}<br>
          <br>
          {{ $t('Do not write @INLINE before the code.') }}
        </div>
      </v-tooltip>
    </v-sheet>
  </div>
</template>

<script>
import {codemirror} from 'vue-codemirror';
import {mapGetters} from 'vuex';

export default {
  name: 'FieldValue',
  props: {
    field: {type: Object, required: true},
  },
  components: {
    VueCodemirror: codemirror
  },
  data: () => ({
    rerender: true,
    code: false,
    cmOptions: {
      taSize: 4,
      mode: {
        name: 'smarty',
        baseMode: 'text/html',
        version: 3,
      },
      line: true,
      lineNumbers: true,
      lineWrapping: true
    },
  }),
  computed: {
    ...mapGetters(['dataColumnsForGroup']),
    ...mapGetters('field', [
      'isSimpleString',
      'isCurrencies',
      'isCategories',
      'isOffers',
      'isShop',
      'isPicture',
      'isRawXml'
    ]),
    classKeys() {
      let group = null;
      if (this.$route.name.indexOf('.categories') !== -1) {
        group = 'categories';
      } else if (this.$route.name.indexOf('.offers') !== -1) {
        group = 'offers';
      }
      return this.dataColumnsForGroup(group);
    },
    value() {
      if (typeof this.field.value === 'object') {
        return this.field.value;
      }
      let found = this.classKeys.find(classKey => classKey.value === this.field.value);
      return {
        value: this.field.value,
        text: found ? found.text : this.field.value
      };
    },
    valuePrepend() {
      if (this.value && this.value.value !== this.value.text) {
        return this.value.value;
      }
      return this.$t('Field');
    },
    openedCode() {
      return this.field.handler || this.code || this.isRawXml(this.field);
    },
  },
  methods: {
    valueSearch(item, queryText, itemText) {
      return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
          || (item && item.value && item.value.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1);
    },
    changedValue(val) {
      let value;
      if (val === null) {
        value = null;
      } else if (Array.isArray(val)) {
        value = val;
      } else if (typeof val === 'object') {
        value = val.value;
      } else { //new value by text
        value = val;
      }
      this.$emit('input', value);
      // if it will be wrong, see https://github.com/vuetifyjs/vuetify/issues/5479#issuecomment-672300135
    },
    toggleCode() {
      if (this.field.handler) {
        return;
      }
      this.code = !this.code;
    },
    removeChip(item) {
      let values = [...this.field.value];
      values.splice(values.indexOf(item.value), 1)
      this.changedValue(values);
    },
    makeFirst(item) {
      let values = [...this.field.value];
      values.splice(values.indexOf(item.value), 1);
      values.unshift(item.value);
      this.changedValue(values);
    },
    countChanged(count) {
      let properties = this.field.properties;
      properties.count = count ? parseInt(count) : 0;
      this.$set(this.field, 'properties', properties)
    }
  },
  mounted() {
    this.code = !!this.field.handler;
    this.rerender = false;
    setTimeout(() => this.rerender = true);
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-field-value .CodeMirror {
  height: auto;
  min-height: 30px;
}

.yandexmarket-field-value .CodeMirror pre.CodeMirror-placeholder {
  color: #999;
}

</style>
