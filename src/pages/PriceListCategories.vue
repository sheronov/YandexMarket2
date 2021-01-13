<template>
  <div class="yandexmarket-pricelist-settings">
    <v-container fluid class="px-0 py-1">
      <v-row no-gutters>
        <v-col md="4">
          <CategoriesTree
              :selected="selected"
              :categories="categories"
              :where="where"
              @contexts:loaded="categories = $event"
              @category:add="categoryAdd"
              @category:remove="categoryRemove"
              @tree:reload="treeReload"
          />
        </v-col>
        <v-col
            cols="12"
            md="6"
        >
          <v-card-text>
            <div class="title font-weight-light grey--text pa-4 text-center">
              Пример выгрузки и другие настройки
            </div>
            <code>
              {{ selected }}
            </code>
          </v-card-text>
        </v-col>
      </v-row>

    </v-container>
  </div>
</template>

<script>
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
    categoryAdd(categoryId, send = true) {
      this.selected.push(categoryId);
      if (send) {
        api.post('mgr/categories/create', {...this.where, category_id: categoryId})
            .then(({data}) => {
              console.log(data);
            })
            .catch(() => {
              this.categoryRemove(categoryId, false);
            })
      }
    },
    categoryRemove(categoryId, send = true) {
      this.selected = this.selected.filter(selected => selected !== categoryId);
      if (send) {
        api.post('mgr/categories/remove', {...this.where, category_id: categoryId})
            .then(({data}) => {
              console.log(data);
            })
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
}
</script>