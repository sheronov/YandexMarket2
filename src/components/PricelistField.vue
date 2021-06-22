<template>
  <v-expansion-panel class="yandexmarket-field" :ref="'panel'+field.id" :readonly="readonly">
    <field-header
        :readonly="readonly"
        :field="field"
        :item="item"
        :color="`grey lighten-${lighten}`"
        :edit="edit"
        :disabledAddAttribute="!!attrs.filter(a => !a.id).length"
        @attribute:add="addAttribute"
        @edit:toggle="toggleEdit"
        @edit:cancel="cancelEdit"
        @field:deleted="$emit('field:deleted',$event)"
        @field:updated="fieldUpdated"
    />
    <v-expansion-panel-content :color="`grey lighten-${lighten}`" eager>
      <v-row class="px-0 pb-1 mb-0" v-if="edit" dense>
        <v-col cols="12" md="6">
          <v-combobox
              :value="field.name"
              @input="changedName"
              :items="availableFields"
              class="yandexmarket-field-tag mr-2"
              placeholder="Введите или выберите из списка"
              :attach="true"
              item-value="value"
              item-text="value"
              hide-details
              solo
              dense
          >
            <template v-slot:prepend-inner>
              <div class="text-no-wrap">
                <code class="mr-2 mt-1 d-inline-block">Элемент:</code>
                <v-icon class="mr-1">icon-angle-left</v-icon>
              </div>
            </template>
            <template v-slot:append>
              <v-icon>icon-angle-right</v-icon>
            </template>
            <template v-slot:item="{item}">
              <code>{{ item.value }}</code>
              <small class="pl-1 grey--text" v-if="item.value !== item.text">{{ item.text }}</small>
            </template>
          </v-combobox>
        </v-col>
        <v-col cols="12" md="6">
          <v-select
              v-model="field.type"
              :items="types"
              class="yandexmarket-field-type"
              :full-width="false"
              label="Тип элемента"
              placeholder="Выберите тип"
              :menu-props="{offsetY: true}"
              :attach="true"
              :_disabled="!!(field.type && !availableType)"
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
      <template v-if="attrs.length">
        <!--        <div class="grey&#45;&#45;text mb-1" style="font-size: 13px;">Атрибуты:</div>-->
        <v-row dense class="mb-1 mt-0">
          <field-attribute v-for="attribute in attrs" :key="attribute.id" :attribute="attribute" v-on="$listeners"/>
        </v-row>
      </template>
      <template v-if="isCategories(field)">
        <div class="text-body-2">Список категорий выбирается на вкладке&nbsp;
          <router-link :to="{name:'pricelist.categories', params:this.$route.params}">Категории и условия</router-link>
        </div>
      </template>
      <template v-else-if="isOffers(field)">
        <div class="text-body-2">Поля товаров настраиваются на вкладке &nbsp;
          <router-link :to="{name:'pricelist.offers', params:this.$route.params}">Поля предложений</router-link>
        </div>
      </template>
      <template v-else-if="isShop(field) && parent === 1">
        <div class="text-body-2">Поля магазина настраиваются на вкладке &nbsp;
          <router-link :to="{name:'pricelist', params:this.$route.params}">Настройки магазина</router-link>
        </div>
      </template>
      <v-card-text class="pa-0" v-else-if="isParent(field)">
        <template v-if="children.length">
          <div class="grey--text" style="font-size: 13px;">Дочерние элементы:</div>
          <v-expansion-panels :value="opened" multiple accordion :key="field.name">
            <pricelist-field
                v-for="child in children"
                :key="child.id"
                :item="child"
                :fields="fields"
                :attributes="attributes"
                :lighten="lighten+1"
                :available-fields="availableFields"
                :available-types="availableTypes"
                v-on="$listeners"
                :parent="field.type"
            />
          </v-expansion-panels>
        </template>
        <v-row class="ma-0 align-center" v-if="!isRoot(field)">
          <div v-if="!children.length" class="grey--text">Ещё нет дочерних элементов</div>
          <v-spacer/>
          <v-btn small class="mt-4" color="white" @click="addField"
                 :disabled="!!children.filter(f => !f.id).length || !item.id">
            <v-icon class="icon-sm" left>icon-plus</v-icon>
            <template v-if="children.filter(f => !f.id).length">
              Сохраните новое поле
            </template>
            <template v-else-if="!item.id">
              Сохраните, чтобы добавлять элементы
            </template>
            <template v-else>
              Добавить элемент в &lt;{{ field.name }}&gt;
            </template>
          </v-btn>
        </v-row>
      </v-card-text>
      <field-value
          v-else-if="!isEmptyType(field)"
          :field="field"
          @input="field.value = $event"
      />
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
import {mapGetters} from 'vuex';
import FieldAttribute from "@/components/FieldAttribute";
import FieldHeader from "@/components/FieldHeader";
import FieldValue from "@/components/FieldValue";

export default {
  name: 'PricelistField',
  components: {
    FieldValue,
    FieldHeader,
    FieldAttribute,
  },
  props: {
    item: {required: true, type: [Object]},
    attributes: {type: Array, default: () => ([])},
    fields: {type: Array, default: () => ([])},
    lighten: {type: Number, default: 2},
    availableFields: {type: Array, default: () => ([])},
    availableTypes: {type: Array, default: () => ([])},
    parent: {default: null},
    readonly: {default: true},
  },
  data: () => ({
    field: {},
    attrs: [],
    children: [],
    opened: [],
    edit: false,
    code: false,
    attributesToAdd: []
  }),
  watch: {
    item: {
      immediate: true,
      handler: function (item) {
        this.field = {...this.field, ...item};
        if (Object.keys(item.properties).length) {
          this.field.properties = {...item.properties};
        }
        this.$nextTick().then(() => this.code = !!this.field.handler)
      }
    },
    fields: {
      immediate: true,
      handler: function (fields) {
        if (this.item.id) {
          this.children = fields
              .filter(field => field.parent === this.item.id).slice()
              .sort((a, b) => a.rank - b.rank);
        }
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
  computed: {
    ...mapGetters('field', [
      'isParent',
      'isEmptyType',
      'findByType',
      'isRoot',
      'isShop',
      'isOffers',
      'isCategories'
    ]),
    availableType() {
      return this.availableTypes.find(type => type.value === this.field.type);
    },
    realType() {
      return this.findByType(this.field.type);
    },
    types() {
      let types = this.availableTypes.slice();
      if (this.realType && !this.availableType) {
        types.push(this.realType);
      }
      return types;
    }
  },
  mounted() {
    if (!this.field.id) {
      this.edit = true;
    }
  },
  methods: {
    fieldUpdated(field) {
      this.$emit('field:updated', field);
      if (Object.keys(this.attributesToAdd).length) {
        for (let name in this.attributesToAdd) {
          if (Object.prototype.hasOwnProperty.call(this.attributesToAdd, name) && this.attributesToAdd[name].required) {
            this.addAttribute(null, field.id, name, this.attributesToAdd[name].type);
          }
        }
      }
    },
    changedName(val) {
      let value;
      if (val === null) {
        value = null;
      } else if (typeof val === 'object') {
        value = val.value;
        if (val.type !== 'undefined') {
          this.field.type = val.type;
          if (val.values) {
            this.field.values = val.values;
            this.field.properties.values = val.values;
          } else {
            delete this.field.values;
            delete this.field.properties.values;
          }
          if (val.attributes) {
            this.attributesToAdd = val.attributes;
          } else {
            this.attributesToAdd = [];
          }
        }
      } else { //новое значение текстом
        value = val;
      }
      this.field.name = value;
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
        rank: Object.keys(this.children).length + 1,
        properties: {custom: true}
      });
    },
    cancelEdit() {
      this.field = {...this.field, ...this.item};
      if (Object.keys(this.item.properties).length) {
        this.field.properties = {...this.item.properties};
      }
    },
    toggleEdit(event, val = !this.edit) {
      if (event && this.$refs['panel' + this.field.id] && this.$refs['panel' + this.field.id].isActive) {
        event.stopPropagation();
      }
      this.edit = val;
    },
    addAttribute(event, field_id = this.field.id, name = '', type = 0, label = 'Новый атрибут') {
      if (event && this.$refs['panel' + this.field.id] && this.$refs['panel' + this.field.id].isActive) {
        event.stopPropagation();
      }
      this.$emit('attribute:created', {
        id: null,
        field_id: field_id,
        handler: null,
        label: label,
        name: name,
        value: null,
        type: type,
        properties: {
          custom: true
        },
      });
    },
  },

}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-field {
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

.v-select.v-select--is-menu-active .v-input__append-inner .icon {
  transform: rotate(0deg);
}

.yandexmarket-field-tag .v-select__slot input {
  text-align: center;
}

.yandexmarket-field .v-expansion-panel-header > *:not(.v-expansion-panel-header__icon) {
  flex-grow: 0;
}

</style>