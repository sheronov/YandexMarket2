<template>
  <v-container>
    <v-row>
      <v-col>
        <v-treeview
            class="yandexmarket-categories"
            ref="categoriesTree"
            :load-children="loadCategories"
            :items="categories"
            :open.sync="opened"
            selection-type="independent"
            item-children="children"
            item-text="text"
            item-key="pk"
            open-on-click
            hoverable
            dense
        >
          <template v-slot:prepend="{ item }">
            <v-simple-checkbox
                v-if="item.selectable"
                color="primary"
                class="ml-1"
                @click.stop=""
                @input="selectCategory($event, item)"
                :value="selected.indexOf(item.pk) !== -1"
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
    loading: false,
    categories: [],
    selected: [],
    opened: []
  }),
  methods: {
    selectCategory(value, category) {
      if (value) {
        this.selected.push(category.pk);
      } else {
        this.selected = this.selected.filter(selected => selected !== category.pk);
      }
    },
    loadCategories(item) {
      this.loading = true;
      const parentNode = this.$refs.categoriesTree.nodes[item.pk];
      console.log(item, parentNode);
      return api.post('mgr/getcategories', {id: item.id})
          .then(({data}) => {
            // if (open) {
            //   parentNode.isOpen = true;
            // }
            item.children = data;
            data.forEach((node) => {
              // this.$refs.categoriesTree.nodes[node.pk] = {...parentNode, item: node, vnode: null};
              if (node.selected) {
                this.selected.push(node.pk);
              }
              if (node.expanded) {

                // console.log('node expanded', JSON.stringify(node));
                this.$nextTick().then(() => {
                  parentNode.isOpen = true;
                  this.loadCategories(node);
                  // setTimeout(() => {
                  //   this.opened.push(node.pk)
                  // }, 100);
                });
                // setTimeout(() => {this.opened.push(node.pk)}, 100);
                // this.$nextTick(() => this.loadCategories(node, true));
              }


            });
          })
          .catch(e => console.error(e))
          .then(() => this.loading = false);
    },
  },
  mounted() {
    api.post('mgr/getcategories', {id: 'root'})
        .then(({data}) => {
          this.categories = data;
          data.forEach(node => {
            if (node.expanded) {
              // setTimeout(() => {
              //   this.opened.push(node.pk)
              // }, 1000);
              this.$nextTick().then(() => this.opened.push(node.pk));
              // setTimeout(() => this.loadCategories(node, true), 10);
              // this.$nextTick(() => this.loadCategories(node, true))
            }
          })
        })
        .catch(e => console.error(e));
  }
}
</script>

<style scoped>
.yandexmarket-categories >>> .v-treeview-node__prepend:empty {
  display: none;
}

.yandexmarket-categories >>> .v-treeview-node__label .v-treeview-node__content {
  margin-left: 0;
}
</style>