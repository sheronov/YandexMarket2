<template>
  <v-expansion-panel class="yandexmarket-offer-field" :ref="'panel'+field.id" readonly>
    <!--  TODO: переделать панели на обычные div, чтобы не закрывались самостоятельно (бесит)  -->
    <v-expansion-panel-header :color="`grey lighten-${lighten}`" hide-actions class="pr-2 pb-1">
      <inline-edit-dialog v-if="!item.is_root">
        <v-btn icon small title="Порядковый номер (нажмите, чтобы изменить)" class="ml-n2">
          #{{ field.rank }}
        </v-btn>
        <template v-slot:input>
          <v-text-field
              :value="field.rank"
              @input="field.rank = $event ? parseInt($event) : 0"
              label="Приоритет"
              single-line
              type="number"
              prepend-icon="icon-sort"
              min="0"
          />
        </template>
      </inline-edit-dialog>
      <span style="padding-left: 1px; padding-right: 4px;">
         &lt;{{ item.name || 'введите элемент' }}&gt;
      </span>
      <span class="pl-1 grey--text">
          <span v-if="item.label.replace(' *','') !== item.name">{{ item.label.replace(' *', '') }}</span>
          ({{
          types.find((type) => type.value === item.type) && types.find((type) => type.value === item.type).text || item.type || 'выберите тип'
        }})
      </span>
      <v-tooltip v-if="item.help" bottom :max-width="400" :close-delay="200" :attach="true">
        <template v-slot:activator="{ on }">
          <v-btn small icon v-on="on" @click.stop="" class="ml-1">
            <v-icon>
              icon-question-circle
            </v-icon>
          </v-btn>
        </template>
        <div class="text-caption" style="white-space: pre-line;">{{ item.help }}</div>
      </v-tooltip>
      <v-spacer/>
      <template v-if="edited">
        <v-btn @click="cancelEdit" small icon title="Отменить изменения" class="ml-1" color="orange darken-1">
          <v-icon>icon-rotate-left</v-icon>
        </v-btn>
        <v-btn @click="saveField" small title="Сохранить изменения" class="ml-2 mb-1" color="secondary" height="26">
          <v-icon left>icon-save</v-icon>
          Сохранить
        </v-btn>
      </template>
      <template v-else>
        <v-btn
            v-if="field.id"
            small depressed
            @click="addAttribute"
            :title="!!attrs.filter(a => !a.id).length ? 'Сначала сохраните новый' : 'Добавить атрибут'"
            color="transparent"
            min-width="40"
            class="px-0"
            :disabled="!!attrs.filter(a => !a.id).length"
        >
          <v-icon class="icon-xs mr-1" color="grey darken-1">icon-plus</v-icon>
          <v-icon class="icon-xs" color="grey darken-1">icon-font</v-icon>
        </v-btn>
        <v-btn v-if="field.id && field.is_editable"
               small icon
               title="Отредактировать название и тип поля"
               @click="toggleEdit"
               :color="edit ? 'secondary': 'default'"
               class="ml-1"
        >
          <v-icon>icon-pencil</v-icon>
        </v-btn>
        <v-btn small icon title="Удалить поле" @click.stop="deleteField" v-if="field.is_deletable" class="ml-1">
          <v-icon>icon-trash</v-icon>
        </v-btn>
      </template>
    </v-expansion-panel-header>
    <v-expansion-panel-content :color="`grey lighten-${lighten}`" eager>
      <template v-if="attrs.length">
        <div class="grey--text mb-1" style="font-size: 13px;">Атрибуты:</div>
        <v-row dense class="mb-1">
          <offer-field-attribute v-for="attribute in attrs" :key="attribute.id" :attribute="attribute"
                                 v-on="$listeners"/>
        </v-row>
      </template>
      <v-row class="px-0 pb-3" v-if="edit" dense>
        <v-col cols="12" md="6">
          <v-combobox
              v-model="field.name"
              :items="tags"
              class="yandexmarket-offer-field-tag text-center mr-2"
              placeholder="Введите или выберите из списка"
              :attach="true"
              item-value="value"
              item-text="text"
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
        <v-col cols="12" md="6">
          <v-select
              v-model="field.type"
              :items="selectableTypes"
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
      <v-card-title v-if="!field.is_parent" class="pa-0 flex-nowrap">
        <v-combobox
            :value="value"
            @input="changedValue"
            :filter="valueSearch"
            :items="columns"
            :attach="true"
            label="Выберите поле товара"
            placeholder='Или введите в формате "Class.key"'
            prepend-inner-icon="icon-list-ul"
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
            <code>{{ item.value }}</code><span class="pl-1">{{ item.text }}</span>
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
            Доступны поля ресурса {$resource.pagetitle}, товаров miniShop2 {$data.price}, опций ms2 {$option.color}, тв
            полей {$tv.tag}.<br>
            Все нужные ТВ-поля, опции будут приджойнены автоматически.<br>
            <br>
            Писать @INLINE перед кодом НЕ нужно.
          </div>
        </v-tooltip>
      </v-sheet>
      <v-card-text class="pa-0" v-if="field.is_parent">
        <template v-if="children.length">
          <div class="grey--text" style="font-size: 13px;">Поля:</div>
          <v-expansion-panels :value="opened" multiple accordion :key="field.name">
            <pricelist-offer-field
                v-for="child in children"
                :key="child.id"
                :item="child"
                :fields="fields"
                :attributes="attributes"
                :lighten="lighten+1"
                v-on="$listeners"
            />
          </v-expansion-panels>
        </template>
        <v-row class="ma-0 align-center">
          <div v-if="!children.length" class="grey--text">Ещё нет дочерних полей</div>
          <v-spacer/>
          <v-btn small class="mt-4" color="white" @click="addField" :disabled="!!children.filter(f => !f.id).length">
            <v-icon class="icon-sm" left>icon-plus</v-icon>
            <template v-if="children.filter(f => !f.id).length">
              Сохраните новое поле
            </template>
            <template v-else>
              Добавить поле в &lt;{{ field.name }}&gt;
            </template>
          </v-btn>
        </v-row>
      </v-card-text>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
import OfferFieldAttribute from "@/components/OfferFieldAttribute";
import InlineEditDialog from "@/components/InlineEditDialog";
import {codemirror} from 'vue-codemirror';
import FieldTypes, {TYPE_OFFER, TYPE_OPTION} from "@/components/FieldTypes";
import api from '@/api';
import VTextField from "vuetify/lib/components/VTextField/VTextField";
import VSelect from "vuetify/lib/components/VSelect/VSelect";
import VCombobox from "vuetify/lib/components/VCombobox/VCombobox";

export default {
  name: 'PricelistOfferField',
  components: {
    VTextField,
    VSelect,
    VCombobox,
    OfferFieldAttribute,
    InlineEditDialog,
    VueCodemirror: codemirror
  },
  props: {
    item: {required: true, type: [Object]},
    attributes: {type: Array, default: () => ([])},
    fields: {type: Array, default: () => ([])},
    lighten: {type: Number, default: 2}
  },
  watch: {
    item: {
      immediate: true,
      handler: function (item) {
        this.field = {...this.field, ...item};
        this.$nextTick().then(() => this.code = !!this.field.handler)
      }
    },
    fields: {
      immediate: true,
      handler: function (fields) {
        this.children = fields
            .filter(field => !field.is_hidden && field.parent === this.item.id).slice()
            .sort((a, b) => a.rank - b.rank);
        this.$nextTick().then(() => this.opened = Object.keys(this.children).map((field, index) => index));
      }
    },
    attributes: {
      immediate: true,
      handler: function (attributes) {
        this.attrs = attributes.filter(attribute => attribute.field_id === this.item.id).slice();
      }
    }
  },
  data: () => ({
    rerender: true,
    field: {},
    attrs: [],
    children: [],
    opened: [],
    edit: false,
    code: false,
    offerType: TYPE_OFFER,
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
      {value: 'modResource.pagetitle', text: 'Заголовок ресурса'},
      {value: 'longtitle', text: 'Расширенный заголовок'},
      {divider: true},
      {header: 'Поля товара'},
      {value: 'Data.price', text: 'Цена товара'},
    ],
    tags: [
      'test',
      'blabla',
      'yes'
    ],
    types: FieldTypes,
  }),
  computed: {
    edited() {
      return JSON.stringify(this.item) !== JSON.stringify(this.field);
    },
    value() {
      if (typeof this.field.value === 'object') {
        return this.field.value;
      }
      let column = this.columns.find(column => column.value === this.field.value);
      return {
        value: this.field.value,
        text: column ? column.text : this.field.value
      };
    },
    valuePrepend() {
      if (this.field.type === TYPE_OPTION) {
        return 'Текст';
      }
      if (this.value && this.value.value !== this.value.text) {
        return this.value.value;
      }
      return 'Поле';
    },
    selectableTypes() {
      return this.types.filter((type) => !Object.prototype.hasOwnProperty.call(type, 'selectable') || type.selectable)
    },
    openedCode() {
      return this.field.handler || this.code;
    },
  },
  mounted() {
    if (!this.field.id) {
      this.edit = true;
    }
    this.rerender = false;
    this.$nextTick().then(() => this.rerender = true);
  },
  methods: {
    valueSearch(item, queryText, itemText) {
      return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
          || (item && item.value && item.value.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1);
    },
    changedValue(value) {
      if (value === null) {
        this.field.value = null;
      } else if (typeof value === 'object') {
        this.field.value = value.value;
      } else {
        //новое значение
        this.field.value = value;
        //TODO: добавить в store VUEX ко всем колонкам товара
      }
      // if it will be wrong, see https://github.com/vuetifyjs/vuetify/issues/5479#issuecomment-672300135
    },
    addField() {
      this.$emit('field:created', {
        id: null,
        name: null,
        type: null,
        label: 'Новое поле',
        text: '',
        help: 'Описание поля можно задать через лексиконы',
        value: null,
        handler: null,
        pricelist_id: this.field.pricelist_id,
        parent: this.field.id,
        is_editable: true,
        is_deletable: true,
        rank: Object.keys(this.children).length + 1,
        properties: {custom: true}
      });
    },
    toggleEdit(event) {
      if (this.$refs['panel' + this.field.id].isActive) {
        event.stopPropagation();
      }
      this.edit = !this.edit;
    },
    addAttribute() {
      // if (this.$refs['panel' + this.field.id].isActive) {
      //   event.stopPropagation();
      // }
      this.$emit('attribute:created', {
        id: null,
        field_id: this.field.id,
        handler: null,
        label: 'Новый атрибут',
        name: '',
        value: null,
        type: 0,
        properties: {
          custom: true
        },
      });
    },
    toggleCode() {
      if (this.field.handler) {
        return;
      }
      this.code = !this.code;
      if (!this.code) {
        this.field.handler = null;
      }
    },
    deleteField() {
      if (!this.field.id) {
        this.$emit('field:deleted', this.field);
        return;
      }
      if (confirm('Вы действительно хотите удалить поле ' + this.field.name + '?')) {
        api.post('fields/remove', {ids: JSON.stringify([this.field.id])})
            .then(() => this.$emit('field:deleted', this.field))
            .catch(error => console.log(error));
      }
    },
    cancelEdit() {
      this.field = {...this.field, ...this.item};
    },
    saveField() {
      api.post(!this.field.id ? 'fields/create' : 'fields/update', this.field)
          .then(({data}) => {
            this.edit = false;
            this.$nextTick().then(() => this.$emit('field:updated', data.object));
          })
          .catch(error => console.error(error));
    },
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