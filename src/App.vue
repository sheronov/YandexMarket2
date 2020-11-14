<template>
  <div id="yandexmarket2-app">
    <br><br>
    <div class="text-center">
      This is YandexMarket2
    </div>
    <div>
      Прайс-листов: {{ total }}<br>
      <pre>
        {{ lists }}
      </pre>
    </div>
    <br><br>
  </div>
</template>

<script>
import api from "./api";

export default {
  name: 'yandexmarket2-app',
  data() {
    return {
      lists: [],
      total: 0
    }
  },
  mounted() {
    api.post('mgr/list/getlist',{start: 0, limit: 20})
        .then(({data}) => {
          this.lists = data.results;
          this.total = data.total;
        })
  }
}
</script>

<style scoped>
#yandexmarket2-app {
  padding: 0 20px;
}

.text-center {
  text-align: center;
}
</style>
