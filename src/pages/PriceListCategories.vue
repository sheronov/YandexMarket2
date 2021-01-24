<template>
  <div class="yandexmarket-pricelist-settings">
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
  props: {
    pricelist: {type: Object, required: true}
  },
  name: 'PriceListCategories',
  components: {CategoriesTree},
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
      this.selected.push(resourceId);
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