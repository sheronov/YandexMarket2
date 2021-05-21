<template>
  <v-sheet class="pa-2 yandexmarket-pricelist-condition" elevation="1">
    <v-row dense class="mr-6">
      <v-col cols="12" sm="4">
        <v-combobox
            :value="column"
            @input="changedColumn"
            :filter="valueSearch"
            :items="classKeys"
            :attach="true"
            :label="columnLabel"
            placeholder='Или введите в формате "Class.key"'
            item-value="value"
            item-text="text"
            class="mb-0"
            :disabled="!edit"
            hide-details
            filled
            dense
            background-color="white"
        >
          <template v-slot:item="{item}">
            <code>{{ item.value }}</code>
            <span class="pl-1">{{ item.text }}</span>
            <small v-if="item.help" class="pl-1 grey--text">&nbsp;({{ item.help }})</small>
          </template>
        </v-combobox>
      </v-col>
      <v-col cols="12" sm="4">
        <v-select
            v-model="condition.operator"
            :label="`Оператор ${operator ? operator.sign : ''}`"
            :items="operatorsList"
            dense
            :menu-props="{offsetY: true}"
            :attach="true"
            filled
            background-color="white"
            class="mb-0"
            :disabled="!edit"
            hide-details
        ></v-select>
      </v-col>
      <v-col cols="12" sm="4">
        <v-combobox
            v-if="selectable"
            :value="condition.value"
            @input="changedValue"
            :filter="valueSearch"
            :items="values"
            :attach="true"
            label="Значение"
            placeholder="Введите или выберите из списка"
            item-value="value"
            item-text="text"
            class="mb-0"
            :disabled="!edit"
            hide-details
            filled
            dense
            background-color="white"
            :multiple="arrayValue"
        ></v-combobox>
        <v-text-field
            v-else-if="!nullValue"
            :value="condition.value"
            @input="changedValue"
            label="Значение"
            placeholder="Введите значение"
            class="mb-0"
            :disabled="!edit"
            hide-details
            filled
            dense
            background-color="white"
        ></v-text-field>
      </v-col>
    </v-row>
    <div class="yandexmarket-pricelist-condition-actions align-center justify-center">
      <template v-if="edited">
        <v-btn @click="cancelEdit" small icon title="Отменить изменения" color="orange darken-1">
          <v-icon>icon-rotate-left</v-icon>
        </v-btn>
        <v-btn @click="saveChanges" small icon title="Сохранить изменения" color="secondary" height="26">
          <v-icon>icon-save</v-icon>
        </v-btn>
      </template>
      <template v-else>
        <v-btn small icon
               title="Отредактировать условие"
               @click="editCondition"
               :color="edit ? 'secondary': 'default'"
        >
          <v-icon>icon-pencil</v-icon>
        </v-btn>
        <v-btn
            small icon
            title="Удалить условие"
            @click.stop="deleteCondition"
        >
          <v-icon>icon-trash</v-icon>
        </v-btn>
      </template>
    </div>
  </v-sheet>
</template>

<script>
import {mapGetters} from "vuex";
import api from "@/api";

export default {
  name: 'PricelistCondition',
  props: {
    item: {required: true, type: Object}
  },
  data: () => ({
    edit: false,
    condition: {},
    values: [],
  }),
  watch: {
    item: {
      immediate: true,
      handler: function (condition) {
        this.condition = {...this.condition, ...condition};
      }
    },
    'condition.column': {
      immediate: true,
      handler: function (column) {
        if (column) {
          this.loadValues();
        }
      }
    }
  },
  computed: {
    ...mapGetters(['operatorsList', 'dataColumnsForGroup']),
    classKeys() {
      let group = null;
      if (this.$route.name.indexOf('.categories') !== -1) {
        group = 'categories';
      } else if (this.$route.name.indexOf('.offers') !== -1) {
        group = 'offers';
      }
      return this.dataColumnsForGroup(group);
    },
    edited() {
      return JSON.stringify(this.item) !== JSON.stringify(this.condition);
    },
    operator() {
      return this.operatorsList.find(op => op.value === this.condition.operator);
    },
    selectable() {
      return !!(this.operator && this.operator.select);
    },
    nullValue() {
      return !!(this.operator && this.operator.valueless);
    },
    arrayValue() {
      return !!(this.operator && this.operator.multiple);
    },
    column() {
      if (typeof this.condition.column === 'object') {
        return this.condition.column;
      }
      let found = this.classKeys.find(classKey => classKey.value === this.condition.column);
      return {
        value: this.condition.column,
        text: found ? found.text : this.condition.column
      };
    },
    columnLabel() {
      if (this.column && this.column.value !== this.column.text) {
        return 'Поле ' + this.column.value;
      }
      return 'Поле';
    },
  },
  methods: {
    deleteCondition() {
      if (!this.condition.id) {
        this.$emit('condition:deleted', this.condition);
        return;
      }
      if (confirm('Вы действительно хотите удалить это условие?')) {
        api.post('conditions/remove', {ids: JSON.stringify([this.condition.id])})
            .then(() => this.$emit('condition:deleted', this.condition))
            .catch(error => console.log(error));
      }
    },
    editCondition() {
      this.edit = !this.edit;
    },
    cancelEdit() {
      this.condition = {...this.condition, ...this.item};
    },
    saveChanges() {
      setTimeout(() => {
        api.post(!this.condition.id ? 'conditions/create' : 'conditions/update', this.condition)
            .then(({data}) => {
              this.edit = false;
              this.$nextTick().then(() => this.$emit('condition:updated', data.object));
            })
            .catch(error => console.error(error))
      }, 10);
    },
    changedColumn(val) {
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
      this.condition.column = value;
      this.condition.value = this.arrayValue ? [] : null;
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
      this.condition.value = value;
    },
    valueSearch(item, queryText, itemText) {
      return itemText.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
          || (item && item.value && item.value.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1);
    },
    loadValues() {
      api.post('lists/values', {column: this.condition.column})
          .then(({data}) => this.values = data.results);
    }
  },
  mounted() {
    if (!this.condition.id) {
      this.edit = true;
    }
  }
}
</script>

<style>
.yandexmarket-pricelist-condition {
  position: relative;
  border-bottom: 1px solid #dddddd !important;
  background-color: #f5f5f5 !important;
}

.yandexmarket-pricelist-condition-actions {
  position: absolute;
  top: 0;
  bottom: 0;
  right: 5px;
  display: flex;
  flex-direction: column;
  max-width: 28px;
}
</style>