<template>
  <div class="yandexmarket-pricelist-categories">
    <h4>{{ $t('Categories involved in file generation - nested categories must also be selected') }}</h4>
    <p class="mb-2">{{ $t('If nothing is selected, the categories of all matching products will be unloaded') }}</p>
    <CategoriesTree
        :selected="selected"
        :categories="categories"
        :where="{pricelist_id: pricelist.id}"
        v-on="$listeners"
        @contexts:loaded="categories = $event"
        @tree:reload="treeReload"
    />

    <template v-if="categoryField">
      <h4 class="mb-1 mt-2">{{ $t('Category element settings in XML') }}</h4>
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
    <v-alert v-else type="info" color="grey" dense border="left">
      {{ $t('An element of type "category" was not found.') }}
      {{ $t('The categories element may have been removed or is not needed in the file.') }}
      {{ $t('Only the conditions will be considered') }}
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
