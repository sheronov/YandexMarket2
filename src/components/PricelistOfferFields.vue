<template>
  <v-expansion-panel class="yandexmarket-offer-field">
    <!--    <v-card outlined  tile >-->
    <!--    <fieldset class="yandexmarket-field-fieldset">-->
    <v-expansion-panel-header color="grey lighten-4">
       <span style="padding-left: 1px; padding-right: 4px;" @click="toggleEdit">
         &lt;{{ tag }}&gt;
      </span>
      <v-btn x-small depressed @click="addAttribute" title="Добавить атрибут" color="grey lighten-3">
        <v-icon class="icon-xs mr-1">icon-plus</v-icon>
        <v-icon class="icon-xs">icon-font</v-icon>
      </v-btn>
      <span class="pl-1 grey--text text-caption" @click="toggleEdit">
          (тип: {{ field.type }})
        </span>
      <v-spacer/>
      <v-btn small icon title="Отредактировать название и тип поля" @click="toggleEdit">
        <v-icon>icon-pencil</v-icon>
      </v-btn>
      <v-btn small icon title="Удалить поле">
        <v-icon>icon-trash</v-icon>
      </v-btn>
    </v-expansion-panel-header>
    <v-expansion-panel-content class="pt-3">
      <offer-field-attributes v-if="field.attributes" :attributes="field.attributes"/>
      <v-row class="pl-0" v-if="edit" dense>
        <v-col>
          <v-combobox
              :value="tag"
              :items="tags"
              @input="changedTag"
              class="yandexmarket-offer-field-tag text-center mr-2"
              label="Укажите элемент"
              placeholder="Введите или выберите из списка"
              background-color="grey lighten-4"
              hide-details
              solo
              flat
              dense
          >
            <template v-slot:prepend-inner>
              <div class="text-no-wrap">
                <code class="mr-2 mt-1 d-inline-block">Элемент:</code>
                <v-icon>icon-angle-left</v-icon>
              </div>

            </template>
            <template v-slot:append>
              <v-icon>icon-angle-right</v-icon>
            </template>
          </v-combobox>
        </v-col>
        <v-col>
          <v-select
              v-if="edit"
              :value="0"
              :items="types"
              @input="changedType"
              class="yandexmarket-offer-field-type mr-3"
              :full-width="false"
              label="Тип элемента"
              placeholder="Выберите тип"
              background-color="grey lighten-4"
              :menu-props="{offsetY: true}"
              hide-details
              solo
              flat
              dense
          >
            <template v-slot:prepend-inner>
              <div class="text-no-wrap mr-2">
                <code>Тип:</code>
              </div>
            </template>
          </v-select>
        </v-col>
      </v-row>
      <v-card-title v-if="field.type !== 'parent'" class="pa-0">
        <v-combobox
            label="Выберите поле товара"
            placeholder='Или введите в формате "Class.key"'
            prepend-inner-icon="icon-list-ul"
            hide-details
            solo
            :items="columns"
            dense
            :value="value"
        >
          <!--          <template v-slot:prepend-inner v-if="columnKey(value)">-->
          <!--            <code class="text-no-wrap mr-0">{{ columnKey(value) }}</code>-->
          <!--          </template>-->
          <template v-slot:item="{item}">
            <code>{{ item.key }}</code><span class="pl-1">{{ item.text }}</span>
          </template>
        </v-combobox>
        <v-btn
            title="Ввести код (вместо выбора столбца)"
            color="grey lighten-3"
            @click="toggleCode"
            class="ml-3"
            min-width="30"
            elevation="0">
          <v-icon>icon-code</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="pl-0 pr-0" v-if="field.fields || field.type==='parent'">
        <b>Дочерние узлы:</b>
        <v-expansion-panels
            v-if="field.fields" class="yandexmarket-offer-field-children" multiple v-model="opened"
            accordion>
          <pricelist-offer-fields
              v-for="(child,childTag) in field.fields"
              :key="childTag"
              :field="child"
              :tag="childTag"
              :values="values"/>
        </v-expansion-panels>
        <v-flex class="flex-row text-right">
          <v-btn small v-if="field.type === 'parent'" class="mt-5">
            <v-icon class="icon-sm" left>icon-plus</v-icon>
            Добавить дочерний узел
          </v-btn>
        </v-flex>
      </v-card-text>
    </v-expansion-panel-content>
    <!--      <legend class="yandexmarket-fieldset-legend-bottom">&lt;/{{ tag }}&gt;</legend>-->
    <!--    </fieldset>-->
    <!--    </v-card>-->
  </v-expansion-panel>
</template>

<script>
import OfferFieldAttributes from "@/components/OfferFieldAttributes";

export default {
  name: 'PricelistOfferFields',
  components: {OfferFieldAttributes},
  props: {
    values: {required: true, type: [Object, Array]},
    field: {required: true, type: Object},
    tag: {required: true, type: String}
  },
  data: () => ({
    hovered: false,
    edit: false,
    code: false,
    opened: [],
    columns: [
      {header: 'Поля ресурса'},
      {key: 'modResource.pagetitle', text: 'Заголовок ресурса'},
      {key: 'longtitle', text: 'Расширенный заголовок'},
      {divider: true},
      {header: 'Поля товара'},
      {key: 'Data.price', text: 'Цена товара'},
    ],
    tags: [
      'test',
      'offer'
    ],
    types: [
      {value: 0, text: 'родитель'},
      {value: 1, text: 'текстовый'}
    ]
  }),
  computed: {
    value() {
      let value = this.values[this.tag];
      if (Array.isArray(value)) {
        value = null;
        // значит параметр множественный, скорее всего param
      } else if (value instanceof Object) {
        //значит есть атрибуты
        value = value.column || value.handler || null;
      }
      return value;
    },
  },
  methods: {
    columnText(item) {
      if (typeof item === 'object') {
        return `<code>${item.key}</code><span class="pl-1">${item.text}</span>`;
      }
      return item;
    },
    hover() {
      this.hovered = true;
    },
    unhover() {
      this.hovered = false;
    },
    addField() {

    },
    toggleEdit() {
      this.edit = !this.edit;
    },
    addAttribute() {

    },
    changedTag(value) {
      this.tag = value;
    },
    changedType() {

    },
    toggleCode() {
      this.code = !this.code;
    },
    columnKey(key) {
      // let key = null;
      // this.columns.forEach(column => {
      //   if (column.key === key) {
      //     key = column.key;
      //   }
      // })
      return key;
    }
  },
  mounted() {
    this.opened = Object.keys(this.field.fields).map((field, index) => index);
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-offer-field {
  position: relative;
  margin-right: -1px;
  margin-top: -1px;
}

.yandexmarket-field-fieldset {
  border-width: 1px;
  border-color: #ddd;
  border-style: solid;
  padding-left: 10px;
  margin-bottom: 10px;
  position: relative;
}

.yandexmarket-field-fieldset legend {
  font-size: 16px;
}

.yandexmarket-fieldset-actions {
  position: absolute;
  top: -24px;
  right: 10px;
  background: #fff;
  padding: 0 4px;
}

.yandexmarket-fieldset-legend-bottom {
  position: absolute;
  font-size: 14px !important;
  bottom: 0;
  background: #fff;
  right: 10px;
  transform: translateY(50%);
  padding: 0 5px;
  line-height: 1;
}

.yandexmarket-offer-field-type {
  /*max-width: 200px !important;*/
  /*font-size: 0.875em !important;*/
}

.yandexmarket-offer-field-tag {
  /*max-width: 180px !important;*/
}

.yandexmarket-offer-field-tag .v-select__slot input {
  /*display: none;*/
}

.v-select.v-select--is-menu-active .v-input__append-inner .icon {
  transform: rotate(90deg);
}

.yandexmarket-offer-field-tag .v-select__slot input {
  text-align: center;
}
</style>