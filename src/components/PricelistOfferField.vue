<template>
  <v-expansion-panel class="yandexmarket-offer-field" :ref="'panel'+field.id" >
    <v-expansion-panel-header color="grey lighten-4" hide-actions>
       <span style="padding-left: 1px; padding-right: 4px;">
         &lt;{{ field.name }}&gt;
      </span>
      <span class="pl-1 grey--text text-caption">
          (тип: {{ types.find((type) => type.value === field.type).text || field.type }})
        </span>
      <v-spacer/>


      <v-btn small depressed @click="addAttribute" title="Добавить атрибут" color="grey lighten-3">
        <v-icon class="icon-xs mr-1">icon-plus</v-icon>
        <v-icon class="icon-xs">icon-font</v-icon>
      </v-btn>
      <v-btn small icon title="Отредактировать название и тип поля" @click="toggleEdit" class="ml-3"
             v-if="field.is_editable">
        <v-icon>icon-pencil</v-icon>
      </v-btn>
      <v-btn small icon title="Удалить поле" @click.stop="deleteField" v-if="field.is_editable" class="ml-3">
        <v-icon>icon-trash</v-icon>
      </v-btn>

    </v-expansion-panel-header>
    <v-expansion-panel-content class="pt-3">
      <!-- TODO: Сделать здесь всё на одном фоне шапки. Так оступы меньше и красивее.     -->
      <offer-field-attributes v-if="field.attributes && Object.keys(field.attributes).length"
                              :attributes="field.attributes"/>
      <v-row class="pl-0" v-if="edit" dense>
        <v-col>
          <v-combobox
              :value="field.name"
              :items="tags"
              @input="changedTag"
              class="yandexmarket-offer-field-tag text-center mr-2"
              label="Укажите элемент"
              placeholder="Введите или выберите из списка"
              background-color="grey lighten-4"
              :attach="true"
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
              :value="field.type"
              :items="types"
              @input="changedType"
              class="yandexmarket-offer-field-type mr-3"
              :full-width="false"
              label="Тип элемента"
              placeholder="Выберите тип"
              background-color="grey lighten-4"
              :menu-props="{offsetY: true}"
              :attach="true"
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
      <v-card-title v-if="!field.is_fieldable" class="pa-0" style="position: relative;">
        <v-combobox
            :value="field.value"
            :items="columns"
            :attach="true"
            label="Выберите поле товара"
            placeholder='Или введите в формате "Class.key"'
            prepend-inner-icon="icon-list-ul"
            hide-details
            solo
            dense
        >
          <template v-slot:item="{item}">
            <code>{{ item.key }}</code><span class="pl-1">{{ item.text }}</span>
          </template>
        </v-combobox>
        <v-btn
            title="Добавить код-обработчик значения"
            color="grey lighten-3"
            @click="toggleCode"
            class="ml-3"
            min-width="30"
            elevation="0">
          <v-icon>icon-code</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="pl-0 pr-0" v-if="field.is_fieldable">
        <b>Дочерние узлы:</b>
        <v-expansion-panels v-model="opened" v-if="field.fields" multiple accordion>
          <pricelist-offer-field
              v-for="(child,key) in field.fields"
              :key="key"
              :field="child"
          />
        </v-expansion-panels>
        <v-flex class="flex-row text-right">
          <v-btn small class="mt-5">
            <v-icon class="icon-sm" left>icon-plus</v-icon>
            Добавить дочерний узел
          </v-btn>
        </v-flex>
      </v-card-text>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
import OfferFieldAttributes from "@/components/OfferFieldAttributes";

export default {
  name: 'PricelistOfferField',
  components: {
    OfferFieldAttributes
  },
  props: {
    field: {required: true, type: [Object]},
  },
  data: () => ({
    edit: false,
    code: false,
    opened: [],
    //TODO: всё ниже нужно грузить из бэкенда вместе с тегами
    columns: [
      {header: 'Поля ресурса'},
      {key: 'modResource.pagetitle', text: 'Заголовок ресурса'},
      {key: 'longtitle', text: 'Расширенный заголовок'},
      {divider: true},
      {header: 'Поля товара'},
      {key: 'Data.price', text: 'Цена товара'},
    ],
    tags: [],
    types: [
      {value: 0, text: 'корневой'},
      {value: 1, text: 'родительский'},
      {value: 2, text: 'магазин'},
      {value: 4, text: 'валюта'},
      {value: 5, text: 'категории'},
      {value: 6, text: 'предложения'},
      {value: 7, text: 'предложение'},
      {value: 8, text: 'текстовая опция'},
      {value: 9, text: 'ещё не реализовано'},
      {value: 10, text: 'строковый'},
      {value: 11, text: 'текст в CDATA'},
      {value: 12, text: 'числовой'},
      {value: 13, text: 'да/нет'},
      {value: 14, text: 'параметр'},
      {value: 15, text: 'изображения'},
    ],
  }),
  methods: {
    addField() {

    },
    toggleEdit(event) {
      if (this.$refs['panel' + this.field.id].isActive) {
        event.stopPropagation();
      }
      this.edit = !this.edit;
    },
    addAttribute(event) {
      if (this.$refs['panel' + this.field.id].isActive) {
        event.stopPropagation();
      }
    },
    changedTag(value) {
      this.tag = value;
    },
    changedType() {

    },
    toggleCode() {
      this.code = !this.code;
    },
    deleteField() {

    }
  },
  mounted() {
    this.opened = Object.keys(this.field.fields || {}).map((field, index) => index);
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

.yandexmarket-offer-field .v-expansion-panel-header > *:not(.v-expansion-panel-header__icon) {
  flex-grow: 0;
}

</style>