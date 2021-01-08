<template>
  <v-container fluid class="px-0">
    <v-row no-gutters>
      <v-col>
        <v-card :loading="loading">
          <v-toolbar flat dense color="grey lighten-3" height="32">
            <span v-if="!selected.length">Выберите категории</span>
            <span v-else>Выбрано {{ declension(selected.length, ['категория', 'категории', 'категорий']) }}</span>
            <v-spacer/>

            <v-btn icon title="Раскрыть все категории" @click="expandTree">
              <v-icon>icon icon-expand</v-icon>
            </v-btn>
            <v-btn icon title="Свернуть все категории" @click="collapseTree">
              <v-icon>icon icon-compress</v-icon>
            </v-btn>
            <v-btn icon title="Обновить дерево" @click="reloadTree">
              <v-icon>icon icon-refresh</v-icon>
            </v-btn>
          </v-toolbar>
          <v-divider/>
          <v-card-text class="pa-0">
            <v-treeview
                :items="categories"
                :load-children="loadCategories"
                :item-text="itemText"
                :item-key="itemKey"
                class="yandexmarket-categories"
                ref="categoriesTree"
                selection-type="independent"
                item-children="children"
                open-on-click
                hoverable
                dense
            >
              <template v-slot:prepend="{ item }">
                <v-simple-checkbox
                    title="Выбрать категорию"
                    v-if="item.selectable"
                    color="primary"
                    class="text-center"
                    @click.stop=""
                    @input="selectCategory($event, item)"
                    :value="selected.indexOf(item[itemKey]) !== -1"
                ></v-simple-checkbox>
              </template>
              <template v-slot:label="{ item }">
                <div class="v-treeview-node__content">
                  <div class="v-treeview-node__prepend">
                    <v-icon v-if="item.iconCls" class="mx-1 v-treeview-label__icon">icon {{ item.iconCls }}</v-icon>
                  </div>
                  <div class="v-treeview-node__label" v-html="item.text"></div>
                </div>
              </template>
            </v-treeview>
          </v-card-text>
        </v-card>
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
</template>

<script>
import api from "@/api";
import {declension} from "@/helpers";

export default {
  name: 'Tree',
  data: () => ({
    itemKey: 'pk',
    itemText: 'text',
    loading: false,
    categories: [],
    selected: [],
    opened: [],
    loaded: [],
    awaitChildren: {}
  }),
  methods: {
    declension(number, title, withNum = true) {
      return declension(number, title, withNum);
    },
    selectCategory(value, category) {
      if (value) {
        this.selected.push(category[this.itemKey]);
        //todo: ajax query for add category
      } else {
        this.selected = this.selected.filter(selected => selected !== category[this.itemKey]);
        //todo: ajax query for remove category
      }
    },
    loadCategories(item) {
      this.loading = true;
      if (this.awaitChildren[item[this.itemKey]]) {
        delete this.awaitChildren[item[this.itemKey]];
      }
      return api.post('mgr/getcategories', {id: item.id})
          .then(({data}) => {
            item.children = data;
            data.forEach((child) => {
              if (child.selected) {
                this.selected.push(child[this.itemKey]);
              }
              if (child.hasChildren) {
                this.awaitChildren[child[this.itemKey]] = true;
              }
              if (child.expanded) {
                setTimeout(() => {
                  this.$refs.categoriesTree.updateOpen(child[this.itemKey], true);
                  // we have to wait before the tree created children nodes
                }, 50);
              }
            });
          })
          .catch(e => console.error(e))
          .then(() => this.loading = false);
    },
    loadContexts() {
      this.loading = true;
      api.post('mgr/getcategories', {id: 'root'})
          .then(({data}) => this.categories = data)
          .catch(e => console.error(e))
          .then(() => {
            this.loading = false
            // by default all context are opening
            this.$nextTick().then(() => this.$refs.categoriesTree.updateAll(true));
          });
    },
    reloadTree() {
      this.categories = [];
      this.selected = [];
      this.loadContexts();
    },
    expandTree() {
      this.$refs.categoriesTree.updateAll(true);
      setTimeout(() => {
        if (Object.keys(this.awaitChildren).length > 0) {
          this.expandTree();
        }
      }, 200); // we can only check after categories loading
    },
    collapseTree() {
      this.$refs.categoriesTree.updateAll(false);
    }
  },
  mounted() {
    this.loadContexts()
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
.yandexmarket-categories >>> *, .yandexmarket-categories >>> .v-treeview-label__icon {
  color: #556C88;
}

.yandexmarket-categories >>> .v-treeview-node__prepend:empty {
  display: none;
}

.yandexmarket-categories >>> .v-treeview-node__content {
  margin-left: 0;
}

.yandexmarket-categories >>> .v-toolbar__title {
  font-size: 1rem;
}

.yandexmarket-categories >>> > .v-treeview-node > .v-treeview-node__root {
  min-height: 32px;
  background: #f6f6f6;
}

</style>