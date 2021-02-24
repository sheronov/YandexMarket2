<template>
  <div class="yandexmarket-field-value">
    <v-card-title class="pa-0 flex-nowrap">
      <template v-if="isSimpleString(field)">
        <v-text-field
            :value="field.value"
            @input="changedValue"
            label="Введите значение"
            placeholder="Попадёт в XML без обработки и замен плейсхолдеров"
            hide-details
            solo
            dense
        >
          <template v-slot:prepend-inner>
            <div class="text-no-wrap mr-2">
              <v-icon left color="inherit">icon-paragraph</v-icon>
              <code style="position:relative; top:1px;">Текст</code>
            </div>
          </template>
        </v-text-field>
      </template>
      <template v-else-if="isCurrencies(field)">
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
      </template>
      <template v-else-if="isCategories(field)">
        <div class="text-body-2">Список категорий выбирается на вкладке&nbsp;
          <router-link :to="{name:'pricelist.categories', params:this.$route.params}">Категории и условия</router-link>
        </div>
      </template>
      <template v-else-if="isOffers(field)">
        <div class="text-body-2">Поля товаров настраиваются на вкладке &nbsp;
          <router-link :to="{name:'pricelist.offers', params:this.$route.params}">Поля предложений</router-link>
        </div>
      </template>
      <template v-else>
        <v-combobox
            :value="value"
            @input="changedValue"
            :filter="valueSearch"
            :items="dataColumns"
            :attach="true"
            label="Выберите поле товара"
            placeholder='или введите в формате "Class.key" и нажмите Enter'
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
            <small v-if="item.help" class="pl-1 grey--text">&nbsp;({{item.help}})</small>
          </template>
        </v-combobox>
        <v-btn
            :title="openedCode ? 'Для закрытия очистите введённый код' :'Добавить код-обработчик значения'"
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
          placeholder="Пример: {$input === 'Да' ? true : false}"
      ></vue-codemirror>
      <v-tooltip left :max-width="450" :close-delay="200" :attach="true">
        <template v-slot:activator="{on}">
          <v-btn v-on="on" x-small fab icon absolute style="right: -4px; top: -4px;">
            <v-icon color="grey darken-1">icon-question-circle</v-icon>
          </v-btn>
        </template>
        <div class="text-caption" style="white-space: pre-line;">
          INLINE обработка поля на Fenom (значение попадает в $input)<br>
          Нужно для приведения к boolean, вырезанию лишних тегов/текстов, обработки массивов, ТВ-полей или для
          независимых значений.
          <br><br>
          Доступны поля ресурса {$resource.pagetitle}, товаров miniShop2 {$data.price}, опций ms2 {$option.color},
          тв
          полей {$tv.tag}.<br>
          Все нужные ТВ-поля, опции будут приджойнены автоматически.<br>
          <br>
          Писать @INLINE перед кодом НЕ нужно.
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
    ...mapGetters(['dataColumns', 'columnText']),
    ...mapGetters('field', [
      'isSimpleString',
      'isCurrencies',
      'isCategories',
      'isOffers',
      'isPictures'
    ]),
    value() {
      if (typeof this.field.value === 'object') {
        return this.field.value;
      }
      return {
        value: this.field.value,
        text: this.columnText(this.field.value) || this.field.value
      };
    },
    valuePrepend() {
      if (this.value && this.value.value !== this.value.text) {
        return this.value.value;
      }
      return 'Поле';
    },
    openedCode() {
      return this.field.handler || this.code;
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
      } else { //новое значение текстом
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
    }
  },
  mounted() {
    this.rerender = false;
    this.$nextTick().then(() => this.rerender = true);
    this.code = !!this.field.handler;
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