<template>
  <v-card class="ym2-main-wrapper" :loading="loading">
    <v-card-title>
      <template v-if="total">У вас {{ decl(total, ['прайс-лист', 'прайс-листа', 'прайс-листов']) }}</template>
      <template v-else>Добавьте свой первый прайс-лист</template>
      <v-spacer></v-spacer>
      <v-btn color="secondary" :disabled="loading" small @click="createPricelist">
        <v-icon left class="icon-sm">icon icon-plus</v-icon>
        Добавить прайс-лист
      </v-btn>
    </v-card-title>
    <v-data-table
        v-if="total"
        :items="lists"
        :headers="headers"
        :items-per-page="pagination.limit"
        :footer-props="{
          itemsPerPageText: 'Показывать по',
          itemsPerPageOptions:[pagination.limit],
          showCurrentPage: true,
          showFirstLastPage: true,
        }"
    >
      <template v-slot:footer.page-text="{pageStart, pageStop, itemsLength}">
        Показано {{ pageStart }}-{{ pageStop }} из {{ itemsLength }}
      </template>
      <template v-slot:item.active="{ value }">
        {{ value ? 'Да' : 'Нет' }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-spacer/>
        <v-btn
            :to="{name:'pricelist', params: {id: item.id}}"
            title="Редактировать прайс-лист"
            icon
        >
          <v-icon>icon icon-pencil</v-icon>
        </v-btn>
        <v-btn
            @click="removePricelist(item)"
            title="Удалить прайс-лист"
            icon
        >
          <v-icon color="red">icon icon-remove</v-icon>
        </v-btn>
      </template>
    </v-data-table>
    <v-card-text v-else>
      Пока ещё нет прайс-листов
    </v-card-text>
  </v-card>
</template>

<script>
import api, {ValidationError} from "./../api";
import {declension} from "@/helpers";

export default {
  name: 'PriceLists',
  data() {
    return {
      loading: false,
      lists: [],
      headers: [
        {text: 'ID', value: 'id'},
        {text: 'Тип', value: 'type'},
        {text: 'Файл', value: 'file'},
        {text: 'Активно', value: 'active'},
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
          .catch(error => console.error(error))
          .then(() => this.loading = false);
    },
    createPricelist() {
      this.loading = true;
      api.post('mgr/pricelists/create', {active: false})
          .then(({data}) => this.$router.push({name: 'pricelist', params: {id: data.object.id}}))
          .catch(error => {
            if (error instanceof ValidationError) {
              console.error(error.getErrors())
            } else {
              console.error(error.toString())
            }
          })
          .then(() => this.loading = false);
    },
    removePricelist(pricelist) {
      // TODO: когда-нибудь красивым подтверждение сделать
      if (confirm(`Вы действительно хотите удалить прайс-лист ${pricelist.id} ? Это действие безвозвратное`)) {
        this.loading = true;
        api.post('mgr/pricelists/remove', {ids: JSON.stringify([pricelist.id])})
            .then(() => this.loadLists())
            .catch(error => console.error(error))
            .then(() => this.loading = false);
      }
    }
  },
  mounted() {
    this.loadLists();
  },
}
</script>
