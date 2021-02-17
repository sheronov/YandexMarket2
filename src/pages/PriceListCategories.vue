<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Категории, используемые в выгрузке</h4>
    <p class="mb-2">Категориям можно указать название для лучшего сопоставления в агрегаторе</p>
    <CategoriesTree
        :selected="selected"
        :categories="categories"
        :where="where"
        @contexts:loaded="categories = $event"
        @category:add="categoryAdd"
        @category:remove="categoryRemove"
        @tree:reload="treeReload"
    />
    {{ selected }}
  </div>
</template>

<script>
// TODO: add here save or cancel changes
import CategoriesTree from "@/components/CategoriesTree";
import api from "@/api";

export default {
  name: 'PriceListCategories',
  props: {
    pricelist: {type: Object, required: true}
  },
  components: {CategoriesTree},
  watch: {
    'pricelist.categories': {
      immediate: true,
      handler: function (categories) {
        this.selected = categories || [];
      }
    }
  },
  data: () => ({
    selected: [],
    categories: []
  }),
  computed: {
    where() {
      return this.pricelist.id ? {pricelist_id: this.pricelist.id} : {};
    }
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'categories');
    },
    categoryAdd(resourceId, send = true) {
      if (this.selected.indexOf(resourceId) === -1) {
        this.selected.push(resourceId);
      }
      if (send) {
        api.post('categories/create', {...this.where, resource_id: resourceId})
            .then(() => this.previewXml())
            .catch(() => this.categoryRemove(resourceId, false))
      }
    },
    categoryRemove(resourceId, send = true) {
      this.selected = this.selected.filter(selected => selected !== resourceId);
      if (send) {
        api.post('categories/remove', {...this.where, resource_id: resourceId})
            .then(() => this.previewXml())
            .catch(() => {
              // можно добавлять назад, если по какой-то причине не удалился
              // this.categoryAdd(categoryId, false);
            })
      }
    },
    treeReload() {
      this.categories = [];
      this.selected = [];
    }
  },
  mounted() {
    this.previewXml()
  }
}
</script>