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
// import axios from 'axios';

export default {
  name: 'yandexmarket2-app',
  data() {
    return {
      lists: [],
      total: 0
    }
  },
  mounted() {
    let data = new FormData();
    data.append('action', 'mgr/list/getlist');
    data.append('start', 0);
    data.append('limit', 20);

    fetch(this.$apiUrl, {
      headers: this.$httpConfig,
      method: 'POST',
      data: data,
      credentials: 'include'
    })
        .then(response => response.json())
        .then(result => {
          console.log(result);
        })

    // axios.request({
    //   url: this.$apiUrl,
    //   method: 'post',
    //   withCredentials: true,
    //   data: data,
    //
    // })
    //     .then(({data}) => {
    //       this.lists = data.results;
    //       this.total = data.total;
    //     })

    // axios.post(this.$apiUrl, data, this.$httpConfig)
    //     .then(({data}) => {
    //       this.lists = data.results;
    //       this.total = data.total;
    //     })
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
