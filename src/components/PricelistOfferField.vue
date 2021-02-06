<template>
  <v-expansion-panel class="yandexmarket-offer-field" :ref="'panel'+field.id" :readonly="field.name !== 'offer'">
    <v-expansion-panel-header :color="`grey lighten-${lighten}`" hide-actions class="pr-2">
      <inline-edit-dialog v-if="field.name !== 'offer'">
        <v-btn icon small title="Порядковый номер (нажмите, чтобы изменить)" class="ml-n2">
          #{{ field.rank }}
        </v-btn>
        <template v-slot:input>
          <v-text-field
              v-model="field.rank"
              label="Приоритет"
              single-line
              type="number"
              prepend-icon="icon-sort"
              min="0"
          />
        </template>
      </inline-edit-dialog>
      <span style="padding-left: 1px; padding-right: 4px;">
         &lt;{{ field.name }}&gt;
      </span>
      <span class="pl-1 grey--text">
          <span v-if="field.label.replace(' *','') !== field.name">{{ field.label.replace(' *', '') }}</span>
          ({{ types.find((type) => type.value === field.type).text || field.type }})
        </span>
      <v-tooltip v-if="field.help" bottom :max-width="400" :close-delay="200" :attach="true">
        <template v-slot:activator="{ on }">
          <v-btn small icon v-on="on" @click.stop="" class="ml-1">
            <v-icon>
              icon-question-circle
            </v-icon>
          </v-btn>
        </template>
        <div class="text-caption" style="white-space: pre-line;">{{ field.help }}</div>
      </v-tooltip>
      <v-spacer/>
      <v-btn small depressed @click="addAttribute" title="Добавить атрибут" color="transparent" min-width="40"
             class="px-0">
        <v-icon class="icon-xs mr-1" color="grey darken-1">icon-plus</v-icon>
        <v-icon class="icon-xs" color="grey darken-1">icon-font</v-icon>
      </v-btn>
      <v-btn small icon title="Отредактировать название и тип поля" @click="toggleEdit" class="ml-1"
             v-if="field.is_editable">
        <v-icon>icon-pencil</v-icon>
      </v-btn>
      <v-btn small icon title="Удалить поле" @click.stop="deleteField" v-if="field.is_deletable" class="ml-1">
        <v-icon>icon-trash</v-icon>
      </v-btn>
    </v-expansion-panel-header>
    <v-expansion-panel-content :color="`grey lighten-${lighten}`">
      <template v-if="field.attributes && Object.keys(field.attributes).length">
        <div class="grey--text mb-1">Атрибуты:</div>
        <offer-field-attributes :attributes="field.attributes"/>
      </template>
      <v-row class="px-0 pb-3" v-if="edit" dense>
        <v-col>
          <v-combobox
              :value="field.name"
              :items="tags"
              @input="changedTag"
              class="yandexmarket-offer-field-tag text-center mr-2"

              placeholder="Введите или выберите из списка"
              :attach="true"

              hide-details
              solo
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
              :items="selectableTypes"
              @input="changedType"
              class="yandexmarket-offer-field-type"
              :full-width="false"
              label="Тип элемента"
              placeholder="Выберите тип"
              :menu-props="{offsetY: true}"
              :attach="true"
              hide-details
              solo
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
          <template v-slot:prepend-inner>
            <div class="text-no-wrap mr-2">
              <code>Поле:</code>
              <v-icon right color="inherit">icon-list-ul</v-icon>
            </div>
          </template>
          <template v-slot:item="{item}">
            <code>{{ item.key }}</code><span class="pl-1">{{ item.text }}</span>
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
      </v-card-title>

      <v-sheet elevation="1" v-if="openedCode" style="position: relative;">
        <codemirror
            v-model="field.handler"
            :options="cmOptions"
            class="mt-3"
            placeholder="Пример: {$input === 'Да' ? 'true' : 'false'}"
        ></codemirror>

        <v-tooltip left :max-width="450" :close-delay="200" :attach="true">
          <template v-slot:activator="{on}">
            <v-btn v-on="on" x-small fab icon absolute style="right: -4px; top: -4px;">
              <v-icon color="grey darken-1">icon-question-circle</v-icon>
            </v-btn>
          </template>
          <div class="text-caption" style="white-space: pre-line;">
            INLINE обработка поля на Fenom (значение попадает в $input)<br>
            Пригодится для приведения к boolean значениям, вырезанию лишних тегов/текстов, обработки массивов или
            ТВ-полей.<br>
            <br>
            Доступны поля ресурса {$resource.pagetitle}, товаров miniShop2 {$data.price}, опций ms2 {$option.color}, тв
            полей {$tv.tag}.<br>
            Все нужные ТВ-поля, опции будут приджойнены автоматически.<br>
            <br>
            Писать @INLINE перед кодом НЕ нужно.
          </div>
        </v-tooltip>
      </v-sheet>
      <v-card-text class="pl-0 pr-0" v-if="field.is_fieldable">
        <div class="grey--text mb-1">Поля:</div>
        <v-expansion-panels :value="opened" v-if="children" multiple>
          <pricelist-offer-field
              :lighten="lighten+1"
              v-for="child in children"
              :key="child.id"
              :field="child"
          />
        </v-expansion-panels>
        <v-flex class="flex-row text-right">
          <v-btn small class="mt-5" color="white">
            <v-icon class="icon-sm" left>icon-plus</v-icon>
            Добавить поле в &lt;{{ field.name }}&gt;
          </v-btn>
        </v-flex>
      </v-card-text>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
import OfferFieldAttributes from "@/components/OfferFieldAttributes";
import InlineEditDialog from "@/components/InlineEditDialog";
import {codemirror} from 'vue-codemirror';


import 'codemirror/mode/smarty/smarty';
import 'codemirror/addon/display/placeholder';

export default {
  name: 'PricelistOfferField',
  components: {
    OfferFieldAttributes,
    InlineEditDialog,
    codemirror
  },
  props: {
    field: {required: true, type: [Object]},
    lighten: {type: Number, default: 2}
  },
  data: () => ({
    edit: false,
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
    //TODO: всё ниже нужно грузить из бэкенда вместе с тегами
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
      'blabla',
      'yes'
    ],
    types: [
      {value: 0, text: 'корневой', selectable: false},
      {value: 2, text: 'магазин', selectable: false},
      {value: 4, text: 'валюта', selectable: false},
      {value: 5, text: 'категории', selectable: false},
      {value: 6, text: 'предложения', selectable: false},
      {value: 7, text: 'предложение', selectable: false},
      {value: 8, text: 'текстовая опция', selectable: false},
      {value: 9, text: 'ещё не реализовано', selectable: false},
      {value: 10, text: 'текст'},
      {value: 11, text: 'текст в CDATA'},
      {value: 12, text: 'число'},
      {value: 13, text: 'да/нет'},
      {value: 14, text: 'параметр'},
      {value: 15, text: 'изображения'},
      {value: 1, text: 'родительский'},
    ],
  }),
  computed: {
    selectableTypes() {
      return this.types.filter((type) => !Object.prototype.hasOwnProperty.call(type, 'selectable') || type.selectable)
    },
    opened() {
      return Object.keys(this.field.fields || {}).map((field, index) => index)
    },
    openedCode() {
      return this.field.handler || this.code;
    },
    children() {
      if (!this.field.fields || !Object.keys(this.field.fields).length) {
        return [];
      }
      return Object.values(this.field.fields).sort((a, b) => a.rank - b.rank);
    }
  },
  mounted() {
    if (this.field.handler) {
      this.code = true;
    }
  },
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
      if (this.field.handler) {
        return;
      }
      this.code = !this.code;
    },
    deleteField() {

    }
  },

}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-offer-field {
  position: relative;
  margin-right: -1px;
  margin-top: -1px;
}

.yandexmarket-offer-field .CodeMirror {
  height: auto;
  min-height: 30px;
}

.yandexmarket-offer-field .CodeMirror pre.CodeMirror-placeholder {
  color: #999;
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
  transform: rotate(0deg);
}

.yandexmarket-offer-field-tag .v-select__slot input {
  text-align: center;
}

.yandexmarket-offer-field .v-expansion-panel-header > *:not(.v-expansion-panel-header__icon) {
  flex-grow: 0;
}

</style>