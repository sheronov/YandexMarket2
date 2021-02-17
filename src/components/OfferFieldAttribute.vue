<template>
  <v-col :md="edit ? 12 : 4" :sm="edit ? 12 : 6"  cols="12" class="yandexmarket-offer-field-attribute">
    <component
        v-if="!edit"
        :is="item.values ? 'v-select' : 'v-text-field'"
        v-model="item.value"
        :items="item.values"
        :placeholder="item.values ? 'Выберите из списка' : 'Введите значение'"
        :hint="item.handler ? '</> '+item.handler : null"
        :persistent-hint="!!item.handler"
        :hide-details="!item.handler"
        :attach="true"
        :menu-props="{offsetY: true}"
        :label="label"
        class="yandexmarket-offer-field-attribute"
        dense
        outlined
    >
      <template v-slot:append v-if="edited">
        <v-icon @click="cancelChanges" title="Отменить изменения" color="orange darken-1">icon-rotate-left</v-icon>
      </template>
      <template v-slot:append>
        <v-btn small v-if="!edited" icon :title="'Отредактировать свойства '+item.name" @click.stop="editAttr"
               class="mt-n1 mr-n2">
          <v-icon>icon-pencil</v-icon>
        </v-btn>
        <v-btn v-else small icon @click.stop="saveChanges" title="Сохранить изменения" class="mt-n1 mr-n2"
               color="secondary">
          <v-icon>icon-save</v-icon>
        </v-btn>
      </template>
    </component>
    <v-sheet v-else elevation="1" class="pt-1 px-2" color="grey lighten-5">
      <v-row class="ma-0">
        <div v-if="attribute.id">Редактирование атрибута {{ attribute.name }}</div>
        <div v-else>Добавление нового атрибута
          <v-tooltip bottom :max-width="400" :close-delay="200" :attach="true">
            <template v-slot:activator="{ on }">
              <v-btn small icon v-on="on" class="mt-n1">
                <v-icon>icon-question-circle</v-icon>
              </v-btn>
            </template>
            <div class="text-caption" style="white-space: pre-line;">Описание атрибута можно задать через лексиконы
            </div>
          </v-tooltip>
        </div>
        <v-spacer/>
        <template v-if="edited">
          <v-btn @click="cancelChanges" small icon title="Отменить изменения" class="ml-1" color="orange darken-1">
            <v-icon>icon-rotate-left</v-icon>
          </v-btn>
          <v-btn @click="saveChanges" small title="Сохранить изменения" class="ml-2 mb-1" color="secondary" height="26">
            <v-icon left>icon-save</v-icon>
            Сохранить
          </v-btn>
        </template>
        <template v-else>
          <v-btn v-if="item.id" small icon title="Отменить редактирование" @click="editAttr" class="ml-1" color="secondary">
            <v-icon>icon-pencil</v-icon>
          </v-btn>
          <v-btn small icon title="Удалить поле" @click="deleteAttribute" class="ml-1">
            <v-icon>icon-trash</v-icon>
          </v-btn>
        </template>
      </v-row>
      <v-row dense class="mt-1">
        <v-col md="4">
          <v-text-field
              v-model="item.name"
              label="Параметр *"
              placeholder="латиницей"
              dense
              required
          ></v-text-field>
        </v-col>
        <v-col md="4">
          <v-select
              v-model="item.type"
              label="Тип значения"
              :items="items"
              dense
          ></v-select>
        </v-col>
        <v-col md="4">
          <component
              :is="item.values ? 'v-select' : 'v-text-field'"
              :value="item.value"
              @input="item.value = $event"
              :items="item.values"
              :label="item.values ? 'Выберите из списка' : 'Введите значение'"
              placeholder="Поле товара"
              hide-details
              :attach="true"
              :menu-props="{offsetY: true}"
              class="yandexmarket-offer-field-attribute"
              dense
          >
            <template v-slot:append-outer v-if="item.type !== 3">
              <v-btn
                  :title="openedCode ? 'Для закрытия очистите введённый код' :'Добавить код-обработчик значения'"
                  :color="openedCode ? 'secondary' : 'accent'"
                  @click="toggleCode"
                  small
                  class="px-2 text-center mt-n1"
                  min-width="30"
              >
                <v-icon>icon-code</v-icon>
              </v-btn>
            </template>
          </component>
        </v-col>
      </v-row>
      <vue-codemirror
          v-if="openedCode"
          class="pb-2 mt-n2"
          v-model="item.handler"
          :options="cmOptions"
          placeholder="Пример: {$input === 'Да' ? true : false}"
      ></vue-codemirror>
    </v-sheet>
  </v-col>
</template>

<script>
import VTextField from "vuetify/lib/components/VTextField/VTextField";
import VSelect from "vuetify/lib/components/VSelect/VSelect";
import VCombobox from "vuetify/lib/components/VCombobox/VCombobox";
import {codemirror} from 'vue-codemirror';
import api from "@/api";

export default {
  name: 'OfferFieldAttribute',
  components: {
    VSelect,
    VCombobox,
    VTextField,
    VueCodemirror: codemirror
  },
  props: {
    attribute: {required: true, type: [Object]}
  },
  data: () => ({
    code: false,
    item: {},
    edit: false,
    items: [
      {value: 0, text: 'строка'},
      {value: 1, text: 'дата'},
      {value: 2, text: 'да/нет'},
      {value: 3, text: 'без подстановки'},
    ],
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
  watch: {
    attribute: {
      immediate: true,
      handler: function (attribute) {
        this.item = {...this.item, ...attribute};
        this.$nextTick().then(() => this.code = !!this.item.handler)
      }
    }
  },
  computed: {
    edited() {
      return JSON.stringify(this.item) !== JSON.stringify(this.attribute);
    },
    label() {
      let label = this.item.name;
      if (this.item.label && this.item.label !== label) {
        label += ` (${this.item.label})`;
      }
      return label;
    },
    openedCode() {
      return !!this.item.handler || this.code;
    },
  },
  methods: {
    editAttr() {
      this.edit = !this.edit;
    },
    saveChanges() {
      setTimeout(() => {
        api.post(!this.item.id ? 'attributes/create' : 'attributes/update', this.item)
            .then(({data}) => {
              this.edit = false;
              this.$nextTick().then(() => this.$emit('attribute:updated', data.object));
            })
            .catch(error => console.error(error))
      }, 50);
    },
    cancelChanges() {
      this.item = {...this.item, ...this.attribute};
    },
    deleteAttribute() {
      if (!this.item.id) {
        this.$emit('attribute:deleted', this.item);
        return;
      }
      if (confirm('Вы действительно хотите удалить атрибут ' + this.item.name + '?')) {
        api.post('attributes/remove', {ids: JSON.stringify([this.item.id])})
            .then(() => this.$emit('attribute:deleted', this.item))
            .catch(error => console.log(error));
      }
    },
    toggleCode() {
      if (this.item.handler) {
        return;
      }
      this.code = !this.code;
      if (!this.code) {
        this.item.handler = null;
      }
    },
  },
  mounted() {
    if (!this.attribute.id) {
      this.edit = true;
    }
  }
}
</script>