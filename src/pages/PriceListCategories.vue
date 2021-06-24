<template>
  <div class="yandexmarket-pricelist-categories">
    <h4>Категории, участвующие в выгрузке - вложенные тоже нужно выбирать</h4>
    <p class="mb-2">Если ничего не выбрать, то будут выгружены категории всех подходящих товаров</p>
    <CategoriesTree
        :selected="selected"
        :categories="categories"
        :where="{pricelist_id: pricelist.id}"
        v-on="$listeners"
        @contexts:loaded="categories = $event"
        @tree:reload="treeReload"
    />

    <template v-if="categoryField">
      <h4 class="mb-1 mt-2">Настройки элемента категории в XML</h4>
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
    </template>
    <v-alert v-else type="warning">
      Не найден элемент с типом category(7). Возможно, был удалён элемент categories. Пересоздайте прайс-лист.
    </v-alert>
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
