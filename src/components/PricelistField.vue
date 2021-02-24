<template>
  <v-expansion-panel class="yandexmarket-field" :ref="'panel'+field.id" readonly>
    <field-header
        :field="field"
        :item="item"
        :color="`grey lighten-${lighten}`"
        :edit="edit"
        :disabledAddAttribute="!!attrs.filter(a => !a.id).length"
        @attribute:add="addAttribute"
        @edit:toggle="toggleEdit"
        @edit:cancel="cancelEdit"
        @field:deleted="$emit('field:deleted',$event)"
        @field:updated="$emit('field:updated',$event)"
    />
    <v-expansion-panel-content :color="`grey lighten-${lighten}`" eager>
      <template v-if="attrs.length">
        <div class="grey--text mb-1" style="font-size: 13px;">Атрибуты:</div>
        <v-row dense class="mb-1">
          <field-attribute v-for="attribute in attrs" :key="attribute.id" :attribute="attribute" v-on="$listeners"/>
        </v-row>
      </template>
      <v-row class="px-0 pb-3" v-if="edit" dense>
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
              :items="availableTypes"
              class="yandexmarket-field-type"
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
      <field-value :field="field" @input="field.value = $event"/>
      <v-card-text class="pa-0" v-if="isParent(field)">
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
              Добавить элемент в &lt;{{ field.name }}&gt;
            </template>
          </v-btn>
        </v-row>
      </v-card-text>
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
  },
  data: () => ({
    field: {},
    attrs: [],
    children: [],
    opened: [],
    edit: false,
    code: false,
  }),
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
            .filter(field => field.parent === this.item.id).slice()
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
  computed: {
    ...mapGetters('marketplace', ['getFields']),
    ...mapGetters('field', [
      'isParent',
    ]),
  },
  mounted() {
    if (!this.field.id) {
      this.edit = true;
    }
  },
  methods: {
    changedName(val) {
      let value;
      if (val === null) {
        value = null;
      } else if (typeof val === 'object') {
        value = val.value;
        if (val.type) {
          this.field.type = val.type;
          // TODO: здесь не только сеттить type, а ещё атрибуты добавить, после сохранения (если есть в поле-объекте)
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
    },
    toggleEdit(edit) {
      this.edit = edit;
    },
    addAttribute() {
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