<template>
  <v-card :loading="loading" flat tile>
    <v-toolbar flat color="grey lighten-2" dense height="40">
      <span v-if="!selected.length">Выберите категории для выгрузки</span>
      <span v-else>Выбрано {{ declension(selected.length, ['категория', 'категории', 'категорий']) }}</span>
      <v-spacer/>
      <v-btn small icon title="Раскрыть все категории" @click="expandTree">
        <v-icon>icon icon-expand</v-icon>
      </v-btn>
      <v-btn small icon title="Свернуть все категории" @click="collapseTree">
        <v-icon>icon icon-compress</v-icon>
      </v-btn>
      <v-btn small icon title="Обновить дерево" @click="reloadTree">
        <v-icon>icon icon-refresh</v-icon>
      </v-btn>
    </v-toolbar>
    <v-card-text class="px-0 pt-0 pb-2">
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
            <div class="v-treeview-action" v-if="item.hasChildren">
              <v-btn icon small @click.stop="recursiveSelect(item)" title="Выбрать со всеми подкатегориями">
                <v-icon>icon icon-magic</v-icon>
              </v-btn>
            </div>
          </div>
        </template>
      </v-treeview>
    </v-card-text>
  </v-card>
</template>

<script>
import api from "@/api";
import {declension} from "@/helpers";

export default {
  name: 'CategoriesTree',
  props: {
    selected: {
      type: Array,
      default: () => ([]),
    },
    categories: {
      type: Array,
      default: () => ([])
    },
    where: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    itemKey: 'pk',
    itemText: 'text',
    loading: false,
    opened: [],
    loaded: [],
    awaitChildren: {}
  }),
  methods: {
    declension(number, title, withNum = true) {
      return declension(number, title, withNum);
    },
    selectCategory(value, category, send = true) {
      if (value) {
        this.$emit('category:added', category[this.itemKey], send);
        category.selected = true;
      } else {
        this.$emit('category:removed', category[this.itemKey], send);
        category.selected = false;
      }
    },
    recursiveSelect(item) {
      let context = this.categories.find((context) => context.pk === item.ctx);
      if (!context) {
        return false;
      }
      let category = this.findNode(item[this.itemKey], context);
      if (!category) {
        return false;
      }

      this.loadCategories(category)
          .then((children) => {
            if (category.hasChildren && this.$refs.categoriesTree) {
              this.$refs.categoriesTree.updateOpen(category[this.itemKey], true);
            }
            this.selectCategory(!category.selected, category, true);
            if (children && children.length) {
              children.forEach(child => {
                this.$nextTick().then(() => this.recursiveSelect(child));
              });
            }
          });
    },
    loadCategories(item) {
      this.loading = true;
      if (this.awaitChildren[item[this.itemKey]]) {
        delete this.awaitChildren[item[this.itemKey]];
      }
      return api.post('categories/getlist', {...this.where, id: item.id})
          .then(({data}) => {
            this.$set(item, 'children', data);
            data.forEach((child) => {
              if (child.selected) {
                this.$emit('category:added', child[this.itemKey], false);
              }
              if (child.hasChildren) {
                this.awaitChildren[child[this.itemKey]] = true;
              }
              if (child.expanded && this.$refs.categoriesTree) {
                setTimeout(() => {
                  this.$refs.categoriesTree.updateOpen(child[this.itemKey], true);
                  // we have to wait before the tree created children nodes
                }, 50);
              }
            });
            this.loading = false;
            return data;
          })
          .catch(e => {
            console.error(e);
            this.loading = false
          });
    },
    loadContexts() {
      this.loading = true;
      api.post('categories/getlist', {...this.where, id: 'root'})
          .then(({data}) => this.$emit('contexts:loaded', data))
          .catch(e => console.error(e))
          .then(() => {
            this.loading = false
            // by default all context are opening
            this.$nextTick().then(() => this.$refs.categoriesTree.updateAll(true));
          });
    },
    reloadTree() {
      this.$emit('tree:reload');
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
    },
    findNode(key, currentNode) {
      let i, currentChild, result;

      if (key === currentNode[this.itemKey]) {
        return currentNode;
      } else if (currentNode.children) {
        for (i = 0; i < currentNode.children.length; i += 1) {
          currentChild = currentNode.children[i];

          result = this.findNode(key, currentChild);

          if (result !== false) {
            return result;
          }
        }
      }
      return false;
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