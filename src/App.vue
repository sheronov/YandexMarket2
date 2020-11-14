<template>
  <div id="yandexmarket2-app" data-vuetify>
    <v-app style="background: transparent;">
      <v-main>
        <h4 class="text-h4 mb-3">
          YandexMarket2
          <span class="subtitle-1">Прайс-листов: {{ total }}</span>
        </h4>
        <v-data-table
            :items="lists"
            :headers="headers"
            :items-per-page="pagination.limit"
            class="elevation-1"
        >
        </v-data-table>
      </v-main>
    </v-app>
  </div>
</template>

<script>
import api from "./api";

export default {
  name: 'yandexmarket2-app',
  data() {
    return {
      lists: [],
      headers: [
        {text: 'ID', value: 'id'},
        {text: this.$t('name'), value: 'name'},
        {text: 'Описание', value: 'description'},
      ],
      total: 0,
      pagination: {
        start: 0,
        limit: 20
      }
    }
  },
  methods: {
    loadLists() {
      api.post('mgr/list/getlist', this.pagination)
          .then(({data}) => {
            this.lists = data.results;
            this.total = data.total;
          })
    }
  },
  mounted() {
    this.loadLists();
  },
};
</script>

<style scoped>
#yandexmarket2-app {
  padding: 0 20px;
}
</style>
