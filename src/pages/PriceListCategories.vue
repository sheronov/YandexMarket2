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

            :item="categoryField"
            :fields="pricelist.fields"
            :attributes="pricelist.attributes"
            :lighten="3"
            v-on="$listeners"
            :available-fields="[]"
            :available-types="[]"
        />
      </v-expansion-panels>
      <p>Для выбора столбца для названия категории пока доступны только поля ресурса.
        <br/>Значения ТВ-полей можно получить в Fenom-обработчике <code>{$resource.id|resource:'tvname'}</code>
      </p>
    </template>
  </div>
</template>

<script>
import CategoriesTree from "@/components/CategoriesTree";
import PricelistField from "@/components/PricelistField";


export default {
  name: 'PriceListCategories',
  props: {
    pricelist: {type: Object, required: true}
  },
  components: {
    CategoriesTree,
    PricelistField
  },
  data: () => ({
    openedFields: [0],
    selected: [],
    categories: [],
  }),
  watch: {
    'pricelist.categories': {
      immediate: true,
      handler: function (categories) {
        this.selected = categories || [];
      }
    },
  },
  computed: {
    categoryField() {
      return this.pricelist.fields.find(field => field.type === 7);
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
  },
  mounted() {
    this.previewXml()
  }
}
</script>
