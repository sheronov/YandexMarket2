<template>
  <v-card class="ym2-main-wrapper" :loading="loading">
    <v-card-title>
      <template v-if="total">У вас {{ decl(total, ['прайс-лист', 'прайс-листа', 'прайс-листов']) }}</template>
      <template v-else>Добавьте свой первый прайс-лист</template>
      <v-spacer></v-spacer>
      <v-dialog
          v-model="dialog"
          max-width="400px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="secondary" :disabled="loading" small v-on="on" v-bind="attrs">
            <v-icon left class="icon-sm">icon icon-plus</v-icon>
            Добавить прайс-лист
          </v-btn>
        </template>
        <v-card :loading="loading">
          <v-card-title>
            <span class="headline">Добавление прайс-листа</span>
          </v-card-title>
          <v-card-text class="pb-1">
            <v-select
                v-model="pricelist.type"
                hint="Вы можете добавить новые, см. документацию"
                persistent-hint
                class="mb-5"
                :items="marketplaces"
                :error="!!errors['type']"
                :error-messages="errors['type']"
                label="Маркетплейс"
                :menu-props="{offsetY: true}"
                :attach="true"
            ></v-select>
            <v-text-field
                v-model="pricelist.name"
                :error="!!errors['name']"
                :error-messages="errors['name']"
                label="Название прайс-листа"
            ></v-text-field>
            <v-text-field
                v-model="pricelist.file"
                :error="!!errors['file']"
                :error-messages="errors['file']"
                label="Имя файла"
            ></v-text-field>
          </v-card-text>
          <v-card-actions class="pb-4">
            <v-btn text @click="closeDialog">
              Отмена
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn color="secondary" @click="createPricelist">
              Добавить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
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
        <span>Показано {{ pageStart }}-{{ pageStop }} из {{ itemsLength }}</span>
        <v-btn icon class="ml-6 mr-n5" title="Обновить" @click="loadLists">
          <v-icon>icon icon-refresh</v-icon>
        </v-btn>
      </template>
      <template v-slot:item.active="{ value }">
        {{ value ? 'Да' : 'Нет' }}
      </template>
      <template v-slot:item.generated_on="{ value }">
        {{ value ? value.date.replace('.000000', '') : 'Файл ещё не сгенерирован' }}
      </template>
      <template v-slot:item.type="{ value }">
        {{ marketplaceText(value) || value }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-spacer/>
        <v-btn
            :to="{name:'pricelist.generate', params: {id: item.id}}"
            title="Сгенерировать файл"
            icon
        >
          <v-icon>icon icon-print</v-icon>
        </v-btn>
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
          <v-icon>icon icon-trash</v-icon>
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
import {mapGetters, mapState} from 'vuex';

export default {
  name: 'PriceLists',
  data() {
    return {
      defaultItem: {
        file: 'goods.xml',
        name: 'Все товары',
        type: 'yandex.market'
      },
      pricelist: {
        file: '',
        name: '',
        type: '',
      },
      dialog: false,
      loading: false,
      lists: [],
      headers: [
        {text: 'ID', value: 'id', sortable: false},
        {text: 'Название', value: 'name', sortable: false},
        {text: 'Тип прайс-листа', value: 'type', sortable: false},
        {text: 'Файл', value: 'file', sortable: false},
        {text: 'Обновлён', value: 'generated_on', sortable: false},
        {text: 'Активен', value: 'active', sortable: false},
        {text: 'Действия', value: 'actions', sortable: false, align: 'end'}
      ],
      errors: {},
      total: 0,
      pagination: {
        start: 0,
        limit: 20
      },
    }
  },
  computed: {
    ...mapState('marketplace', ['marketplaces']),
    ...mapGetters('marketplace', ['marketplaceText'])
  },
  methods: {
    decl(number, titles, withNum = true) {
      return declension(number, titles, withNum);
    },
    loadLists() {
      this.loading = true;
      api.post('pricelists/getlist', this.pagination)
          .then(({data}) => {
            this.lists = data.results;
            this.total = data.total;
          })
          .catch(error => console.error(error))
          .then(() => this.loading = false);
    },
    closeDialog() {
      this.errors = {};
      this.dialog = false;
      this.pricelist = {...this.defaultItem};
    },
    createPricelist() {
      this.errors = {};
      this.loading = true;
      api.post('pricelists/create', {...this.pricelist, active: false})
          .then(({data}) => {
            this.$router.push({name: 'pricelist', params: {id: data.object.id}});
            this.pricelist = {...this.defaultItem};
          })
          .catch(error => {
            if (error instanceof ValidationError) {
              let errors = new Map(error.getErrors().map(err => {
                return Object.entries(err).slice(0, 2).map(errArr => errArr[1] || '');
              }));
              this.errors = Object.fromEntries(errors);
            } else {
              console.error(error.toString())
            }
          })
          .then(() => {
            this.loading = false;
          });
    },
    removePricelist(pricelist) {
      if (confirm(`Вы действительно хотите удалить прайс-лист ${pricelist.id} ? Это действие безвозвратное`)) {
        this.loading = true;
        api.post('pricelists/remove', {ids: JSON.stringify([pricelist.id])})
            .then(() => this.loadLists())
            .catch(error => console.error(error))
            .then(() => this.loading = false);
      }
    }
  },
  mounted() {
    this.loadLists();
    this.pricelist = {...this.defaultItem};
  },
}
</script>
