<template>
  <v-container>
    <v-row>
      <v-col>
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
                v-if="item.selectable"
                color="primary"
                class="ml-2"
                @click.stop=""
                @input="selectCategory($event, item)"
                :value="selected.indexOf(item[itemKey]) !== -1"
                :ripple="false"
            ></v-simple-checkbox>
          </template>
          <template v-slot:label="{ item }">
            <div class="v-treeview-node__content">
              <div class="v-treeview-node__prepend">
                <v-icon v-if="item.iconCls" class="ma-1">icon {{ item.iconCls }}</v-icon>
              </div>
              <div class="v-treeview-node__label" v-html="item.text"></div>
            </div>
          </template>
        </v-treeview>
      </v-col>
      <v-divider vertical></v-divider>
      <v-col
          cols="12"
          md="6"
      >
        <v-card-text>
          <div class="title font-weight-light grey--text pa-4 text-center">
            Выбранные категории
          </div>

          {{ selected }}
        </v-card-text>
      </v-col>
    </v-row>

  </v-container>
</template>

<script>
import api from "@/api";

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
  }),
  methods: {
    selectCategory(value, category) {
      if (value) {
        this.selected.push(category[this.itemKey]);
      } else {
        this.selected = this.selected.filter(selected => selected !== category[this.itemKey]);
      }
    },
    loadCategories(item) {
      this.loading = true;
      return api.post('mgr/getcategories', {id: item.id})
          .then(({data}) => {
            item.children = data;
            data.forEach((child) => {
              if (child.selected) {
                this.selected.push(child[this.itemKey]);
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
  },
  mounted() {
    this.loading = true;
    api.post('mgr/getcategories', {id: 'root'})
        .then(({data}) => this.categories = data)
        .catch(e => console.error(e))
        .then(() => {
          this.loading = false
          // by default all context are opening
          this.$nextTick().then(() => this.$refs.categoriesTree.updateAll(true));
        });
  }
}
</script>

<style scoped>
.yandexmarket-categories >>> .v-treeview-node__prepend:empty {
  display: none;
}

.yandexmarket-categories >>> .v-treeview-node__content {
  margin-left: 0;
}


</style>