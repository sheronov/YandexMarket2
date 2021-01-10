<template>
  <div class="yandexmarket-pricelist-settings">
    <h4>Категория и условия по товарам</h4>
    <v-container fluid class="px-0">
      <v-row no-gutters>
        <v-col>
          <CategoriesTree :selected="selected"/>
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

export default {
  props: {
    pricelist: {type: Object, required: true}
  },
  name: 'PriceListCategories',
  components: {CategoriesTree},
  data: () => ({
    selected: []
  })
}
</script>