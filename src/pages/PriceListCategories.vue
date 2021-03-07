<template>
  <div class="yandexmarket-pricelist-categories">
    <h4>Категории, участвующие в выгрузке - вложенные тоже нужно выбирать</h4>
    <p class="mb-2">Скоро можно будет указать название категориям для лучшего сопоставления в агрегаторе</p>
    <CategoriesTree
        :selected="selected"
        :categories="categories"
        :where="{pricelist_id: pricelist.id}"
        v-on="$listeners"
        @contexts:loaded="categories = $event"
        @tree:reload="treeReload"
    />
    <p>Если не выбрать категории - то будут выгружены все категории подходящих товаров</p>
    <!--    <code>ids: {{ selected.join(',') }}</code>-->
    <template v-if="categoryField">
      <h4 class="mb-1">Настройки элемента категории в XML</h4>
      <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
        <pricelist-field
            :readonly="false"
            :item="categoryField"
            :fields="pricelist.fields"
            :attributes="pricelist.attributes"
            :lighten="3"
            v-on="$listeners"
            :available-fields="[]"
            :available-types="[]"
        />
      </v-expansion-panels>
    </template>
    <div class="yandexmarket-pricelist-where mb-4 mt-2">
      <v-row class="ma-0 align-center">
        <h4>Условия по товарам</h4>
        <v-tooltip bottom :max-width="400" :close-delay="200" :attach="true">
          <template v-slot:activator="{ on }">
            <v-btn small icon v-on="on" class="ml-1">
              <v-icon>
                icon-question-circle
              </v-icon>
            </v-btn>
          </template>
          <div class="text-caption" style="white-space: pre-line;">Доступны все поля товаров, включая ТВ-поля, опции
            ms2. Компонент автоматически присоединит указанные столбцы. Если нужно присоединить сторонние компоненты -
            обратитесь в поддержку
          </div>
        </v-tooltip>
        <v-spacer/>
        <template v-if="editedWhere">
          <v-btn @click="resetWhere" small icon title="Отменить изменения" class="ml-1" color="orange darken-1">
            <v-icon>icon-rotate-left</v-icon>
          </v-btn>
          <v-btn @click="saveWhere" :disabled="hasErrors"
                 small title="Сохранить изменения" class="ml-2 mb-1" color="secondary" height="26">
            <v-icon left>icon-save</v-icon>
            Сохранить
          </v-btn>
        </template>
        <span v-else>в JSON формате</span>
      </v-row>

      <vue-codemirror
          ref="whereCode"
          :value="where"
          @input="changedWhere"
          :options="cmOptions"
          placeholder='Пример: {"Data.price:>":0, "Vendor.name:IN":["Apple","Samsung"], "Option.tags:IN":["телефон","планшет"]}'
      ></vue-codemirror>
    </div>
  </div>
</template>

<script>
import CategoriesTree from "@/components/CategoriesTree";
import PricelistField from "@/components/PricelistField";
import {codemirror} from 'vue-codemirror';
import '@/plugins/jsonlint'
import 'codemirror/addon/lint/lint.css'
import 'codemirror/addon/lint/lint'
import 'codemirror/addon/lint/json-lint'

export default {
  name: 'PriceListCategories',
  props: {
    pricelist: {type: Object, required: true}
  },
  components: {
    CategoriesTree,
    VueCodemirror: codemirror,
    PricelistField
  },
  data: () => ({
    openedFields: [],
    selected: [],
    categories: [],
    where: '',
    hasErrors: true,
    cmOptions: {
      lineNumbers: true,
      mode: 'application/json',
      gutters: ["CodeMirror-lint-markers"],
      lint: false,
      lineWrapping: true
    }
  }),
  watch: {
    where(value) {
      this.$nextTick().then(() => this.$refs.whereCode.codemirror.setOption("lint", !!value))
    },
    'pricelist.categories': {
      immediate: true,
      handler: function (categories) {
        this.selected = categories || [];
      }
    },
    'pricelist.where': {
      immediate: true,
      handler: function (where) {
        this.where = where;
      },
    },
  },
  computed: {
    categoryField() {
      return this.pricelist.fields.find(field => field.type === 7);
    },
    editedWhere() {
      return (this.where || '') !== (this.pricelist.where || '')
    },
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'categories');
    },
    treeReload() {
      // this.reloaded = false;
      this.categories = [];
      // this.$nextTick().then(() => this.reloaded = true);
    },
    changedWhere(where) {
      this.where = where;
      setTimeout(() => {
        let state = this.$refs.whereCode.codemirror.state;
        this.hasErrors = !!(state.lint && state.lint.marked.length);
      }, 610); //lint in CM has 600ms timeout
    },
    saveWhere() {
      this.$emit('pricelist:updated', {where: this.where ? this.where : null});
    },
    resetWhere() {
      this.where = this.pricelist.where || ''
    }
  },
  mounted() {
    this.previewXml()
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-pricelist-where .CodeMirror {
  height: auto;
  min-height: 60px;
  z-index: 0;
}

.yandexmarket-pricelist-where .CodeMirror pre.CodeMirror-placeholder {
  color: #999;
}
</style>