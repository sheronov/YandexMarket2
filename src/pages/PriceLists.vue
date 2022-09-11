<template>
  <v-card class="ym2-main-wrapper">
    <v-card-title>
      <template v-if="total">{{
          $t('You have {total} {pricelists}', {
            total,
            pricelists: decl(total, $t('pricelist|pricelists|pricelists').split('|'), false)
          })
        }}
      </template>
      <template v-else>{{ $t('Add your first pricelist') }}</template>
      <v-spacer></v-spacer>
      <v-dialog
          v-model="dialog"
          max-width="400px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="secondary" :disabled="loading" small v-on="on" v-bind="attrs">
            <v-icon left class="icon-sm">icon icon-plus</v-icon>
            {{ $t('Add pricelist') }}
          </v-btn>
        </template>
        <v-card>
          <v-card-title>
            <span class="headline">{{ $t('Adding a pricelist') }}</span>
          </v-card-title>
          <v-card-text class="pb-1">
            <v-select
                v-model="pricelist.type"
                :hint="$t('You can add new ones, see documentation')"
                persistent-hint
                class="mb-5"
                :items="marketplaces"
                :error="!!errors['type']"
                :error-messages="errors['type']"
                :label="$t('Marketplace')"
                :menu-props="{offsetY: true}"
                :attach="true"
            ></v-select>
            <v-text-field
                v-model="pricelist.name"
                :error="!!errors['name']"
                :error-messages="errors['name']"
                :label="$t('Pricelist name')"
            ></v-text-field>
            <v-text-field
                v-model="pricelist.file"
                :error="!!errors['file']"
                :error-messages="errors['file']"
                :label="$t('Pricelist filename')"
            ></v-text-field>
          </v-card-text>
          <v-card-actions class="pb-4">
            <v-btn text @click="closeDialog">
              {{ $t('Cancel') }}
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn color="secondary" @click="createPricelist">
              {{ $t('Add') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card-title>
    <v-data-table
        class="pricelists-table"
        :items="lists"
        :header-props="{
          sortByText: $t('Sort by')
        }"
        :headers="headers"
        :options.sync="options"
        :server-items-length="total"
        :loading="loading"
        :no-data-text="$t('No pricelists yet')"
        :footer-props="{
          itemsPerPageText: $t('Show by'),
          itemsPerPageOptions:[20, 50, 100],
          showCurrentPage: true,
          showFirstLastPage: true
        }"
    >
      <template v-slot:footer.page-text="{pageStart, pageStop, itemsLength}">
        <span>{{ $t('Displaying {pageStart}-{pageStop} of {itemsLength}', {pageStart, pageStop, itemsLength}) }}</span>
        <v-btn icon class="ml-6 mr-n5" :title="$t('refresh')" @click="loadLists">
          <v-icon>icon icon-refresh</v-icon>
        </v-btn>
      </template>
      <template v-slot:item.active="{ value }">
        {{ value ? $t('Yes') : $t('No') }}
      </template>
      <template v-slot:item.generated_on="{ value }">
        {{ value || $t('File not generated yet') }}
      </template>
      <template v-slot:item.generate_mode="{ value }">
        <template v-if="parseInt(value) === 2">
          {{ $t('Cron only') }}
        </template>
        <template v-else-if="parseInt(value) === 1">
          {{ $t('On the fly') }}
        </template>
        <template v-else>
          {{ $t('Manually') }}
        </template>
      </template>
      <template v-slot:item.type="{ value }">
        {{ marketplaceText(value) || value }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-spacer/>
        <v-btn
            :to="{name:'pricelist.generate', params: {id: item.id}}"
            :title="item.need_generate ? $t('Need to generate the file!') : $t('File generation')"
            :color="item.need_generate ? 'warning' : 'inherit'"
            icon
        >
          <v-icon>icon icon-print</v-icon>
        </v-btn>
        <v-btn
            :to="{name:'pricelist', params: {id: item.id}}"
            :title="$t('Edit pricelist')"
            icon
        >
          <v-icon>icon icon-pencil</v-icon>
        </v-btn>
        <v-btn
            @click="removePricelist(item)"
            :title="$t('Delete pricelist')"
            icon
        >
          <v-icon>icon icon-trash</v-icon>
        </v-btn>
      </template>
    </v-data-table>
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
        name: this.$t('All goods'),
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
        {text: this.$t('ID'), value: 'id', sortable: true},
        {text: this.$t('Name'), value: 'name', sortable: true},
        {text: this.$t('Pricelist type'), value: 'type', sortable: true},
        {text: this.$t('Filename'), value: 'file', sortable: true},
        {text: this.$t('Generation type'), value: 'generate_mode', sortable: true},
        {text: this.$t('Generated on'), value: 'generated_on', sortable: true},
        {text: this.$t('Active'), value: 'active', sortable: true},
        {text: this.$t('Actions'), value: 'actions', sortable: false, align: 'end'}
      ],
      errors: {},
      total: 0,
      options: {
        page: 1,
        itemsPerPage: 20,
        sortBy: [],
        sortDesc: []
      }
    }
  },
  computed: {
    ...mapState('marketplace', ['marketplaces']),
    ...mapGetters('marketplace', ['marketplaceText'])
  },
  watch: {
    options: {
      handler() {
        this.loadLists()
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    decl(number, titles, withNum = true) {
      return declension(number, titles, withNum);
    },
    loadLists() {
      this.loading = true;
      const params = {
        start: (this.options.page - 1) * this.options.itemsPerPage,
        limit: this.options.itemsPerPage,
        sort: this.options.sortBy[0] || 'id',
        dir: Object.prototype.hasOwnProperty.call(this.options.sortDesc, 0) ? (this.options.sortDesc[0] ? 'desc' : 'asc') : 'desc'
      }
      api.post('Pricelists/GetList', params)
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
      api.post('Pricelists/Create', {...this.pricelist, active: false})
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
      if (confirm(this.$t('Are you sure you want to remove the pricelist {pricelist}? This action is irreversible', {pricelist: pricelist.id}))) {
        this.loading = true;
        api.post('Pricelists/Remove', {id: pricelist.id})
            .then(() => this.loadLists())
            .catch(error => console.error(error))
            .then(() => this.loading = false);
      }
    }
  },
  mounted() {
    this.pricelist = {...this.defaultItem};
  },
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.pricelists-table th i.v-icon {
  left: 6px;
  display: inline-block !important;
}
</style>
