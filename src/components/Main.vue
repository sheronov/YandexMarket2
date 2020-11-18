<template>
  <div class="ym2-main-wrapper">
    <span class="subtitle-1">Прайс-листов: {{ total }}</span>
    <v-data-table
        :items="lists"
        :headers="headers"
        :items-per-page="pagination.limit"
        class="elevation-1"
    >
    </v-data-table>
  </div>
</template>

<script>
import api from "./../api";

export default {
  name: 'Main',
  data() {
    return {
      lists: [],
      headers: [
        {text: 'ID', value: 'id'},
        {text: 'Название', value: 'name'},
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
}
</script>
