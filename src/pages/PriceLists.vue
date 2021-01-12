<template>
  <v-card class="ym2-main-wrapper" :loading="loading">
    <v-card-title>
      <template v-if="total">У вас {{ decl(total, ['прайс-лист', 'прайс-листа', 'прайс-листов']) }}</template>
      <template v-else>Добавьте свой первый прайс-лист</template>
      <v-spacer></v-spacer>
      <v-btn color="secondary" small>
        <v-icon left class="icon-sm">icon icon-plus</v-icon>
        Добавить прайс-лист
      </v-btn>
    </v-card-title>
    <v-data-table
        v-if="total && !loading"
        :items="lists"
        :headers="headers"
        :items-per-page="pagination.limit"
        :footer-props="{itemsPerPageText: 'Показывать по'}"
    >
      <template v-slot:item.actions="{ item }">
        <v-spacer/>
        <v-btn
            :to="{name:'pricelist', params: {id: item.id}}"
            title="Редактировать прайс-лист"
            icon
        >
          <v-icon>icon icon-pencil</v-icon>
        </v-btn>
      </template>
    </v-data-table>
    <v-card-text v-else>
      Пока ещё нет прайс-листов
    </v-card-text>
  </v-card>
</template>

<script>
import api from "./../api";
import {declension} from "@/helpers";

export default {
  name: 'PriceLists',
  data() {
    return {
      loading: false,
      lists: [],
      headers: [
        {text: 'ID', value: 'id'},
        {text: 'Название', value: 'name'},
        {text: 'Описание', value: 'description'},
        {text: 'Действия', value: 'actions', sortable: false, align: 'end'}
      ],
      total: 0,
      pagination: {
        start: 0,
        limit: 20
      },
    }
  },
  methods: {
    decl(number, titles, withNum = true) {
      return declension(number, titles, withNum);
    },
    loadLists() {
      this.loading = true;
      api.post('mgr/pricelists/getlist', this.pagination)
          .then(({data}) => {
            this.lists = data.results;
            this.total = data.total;
          })
          .catch(error => console.log(error))
          .then(() => this.loading = false);
    }
  },
  mounted() {
    this.loadLists();
  },
}
</script>
