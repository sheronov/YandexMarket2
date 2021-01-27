<template>
  <div class="yandexmarket-offer-field">
    <!--    <v-card outlined  tile >-->
    <fieldset class="yandexmarket-field-fieldset">
      <legend class="pl-1 pr-1">
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
      </legend>
      <div class="yandexmarket-fieldset-actions">
        <v-btn small icon title="Отредактировать название и тип поля" @click="toggleEdit">
          <v-icon>icon-pencil</v-icon>
        </v-btn>
        <v-btn small icon title="Удалить поле">
          <v-icon>icon-trash</v-icon>
        </v-btn>
      </div>
      <!--        <legend>-->
      <!--          <v-combobox-->
      <!--              :value="tag"-->
      <!--              :items="items"-->
      <!--              @input="changedTag"-->
      <!--              class="flex-grow-0 yandexmarket-offer-field-tag text-center"-->
      <!--              label="Укажите элемент"-->
      <!--              placeholder="Введите или выберите из списка"-->
      <!--              background-color="grey lighten-3"-->
      <!--              hide-details-->
      <!--              solo-->
      <!--              flat-->
      <!--              dense-->
      <!--          >-->
      <!--            <template v-slot:prepend-inner>-->
      <!--              <v-icon>icon-angle-left</v-icon>-->
      <!--            </template>-->
      <!--            <template v-slot:append>-->
      <!--              <v-icon>icon-angle-right</v-icon>-->
      <!--            </template>-->
      <!--          </v-combobox>-->
      <!--        </legend>-->
      <v-card-title class="pa-0">
        <offer-field-attributes v-if="field.attributes" :attributes="field.attributes"/>
      </v-card-title>
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
      <v-card-title v-if="field.type !== 'parent'" class="pl-0 py-2">
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
      <!--   TODO: сделать здесь expansion panel   -->
      <v-card-text class="pl-0 pr-0" style="margin-right: -1px;">
        <div v-if="field.fields" class="yandexmarket-offer-field-children">
          <pricelist-offer-fields
              v-for="(child,childTag) in field.fields"
              :key="childTag"
              :field="child"
              :tag="childTag"
              :values="values"/>
        </div>
        <v-btn small v-if="field.type === 'parent'" class="mb-3 mt-2">
          <v-icon class="icon-sm" left>icon-plus</v-icon>
          Добавить дочерний узел
        </v-btn>
      </v-card-text>
      <legend class="yandexmarket-fieldset-legend-bottom">&lt;/{{ tag }}&gt;</legend>
    </fieldset>
    <!--    </v-card>-->
  </div>
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
    types() {
      return [
        {value: 0, text: 'родитель'},
        {value: 1, text: 'текстовый'},
      ]
    }
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
  bottom: 0;
  background: #fff;
  left: 20px;
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