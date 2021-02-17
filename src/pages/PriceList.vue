<template>
  <div class="yandexmarket-pricelist">
    <v-tabs v-if="id" class="yandexmarket-pricelist-tabs pr-15" background-color="transparent" :height="40">
      <v-tab :to="{name: 'pricelist', params: {id: id}}" :ripple="false" exact>Настройки прайс-листа</v-tab>
      <v-tab :to="{name: 'pricelist.categories', params: {id: id}}" :ripple="false" exact>Категории и условия</v-tab>
      <v-tab :to="{name: 'pricelist.offers', params: {id: id}}" :ripple="false" exact>Поля предложений</v-tab>
      <v-tab :to="{name: 'pricelist.generate', params: {id: id}}" :ripple="false" exact>Выгрузка и параметры</v-tab>
    </v-tabs>
    <v-card class="yandexmarket-pricelist-card" :loading="!pricelist">
      <v-card-text style="min-height: 300px;">
        <v-btn v-if="showPreview"
               @click="togglePreview" fab absolute top right small elevation="1" color="white"
               :title="preview ? 'Отключить предпросмотр' : 'Включить предпросмотр'">
          <v-icon :color="preview ? 'primary' : 'default'">icon-file-code-o</v-icon>
        </v-btn>
        <v-row dense>
          <v-col :md="showPreview && preview ? 7 : 12">
            <router-view
                v-if="pricelist"
                v-bind="{pricelist:pricelist}"
                @preview:xml="previewXml"
                @input="pricelistUpdated"
            ></router-view>
            <v-card-actions class="px-0" v-if="false">
              <v-btn v-if="!hasChanges" :to="{name: 'pricelists'}" exact title="Ко всем прайс-листам">
                <v-icon left>icon-arrow-left</v-icon>
                Вернуться
              </v-btn>
              <v-btn v-else @click="cancelChanges" title="Отменить все изменения">
                <v-icon left>icon-undo</v-icon>
                Отменить
              </v-btn>
              <v-spacer/>
              <v-btn :disabled="!hasChanges" @click="saveChanges" color="secondary">
                <v-icon left>icon-save</v-icon>
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-col>
          <v-col cols="12" md="5" v-if="showPreview && preview">
            <div class="yandexmarket-xml-preview">
              <h4><label for="yandexmarket-preview">Предпросмотр XML элемента &lt;{{ previewType }}&gt;</label></h4>
              <p class="mb-2">Автоматически обновляется при любом изменении</p>
              <!--              <textarea ref="textarea" id="yandexmarket-preview"></textarea>-->
              <codemirror id="yandexmarket-preview" v-model="code" :options="cmOptions"></codemirror>
            </div>
            <pre v-if="debug && Object.keys(debug).length">{{ debug }}</pre>
          </v-col>
        </v-row>
        <loader :status="!pricelist"></loader>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import Loader from "@/components/Loader";
import api from "@/api";
import {codemirror} from 'vue-codemirror';

import 'codemirror/lib/codemirror.css';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/smarty/smarty';
import 'codemirror/addon/display/placeholder';

export default {
  name: 'PriceList',
  components: {Loader, codemirror},
  data: () => ({
    id: null,
    pricelist: null,
    type: 'yandexmarket',
    preview: true,
    previewType: null,
    previewData: {},
    hasChanges: true,
    debug: null,
    code: null,
    cmOptions: {
      lineNumbers: true,
      mode: 'xml',
      cursorBlinkRate: -1,
      readOnly: true,
    }
  }),
  computed: {
    showPreview() {
      return ['pricelist', 'pricelist.categories', 'pricelist.offers'].indexOf(this.$route.name) !== -1;
    }
  },
  methods: {
    pricelistUpdated(pricelist) {
      this.pricelist = {...this.pricelist, ...pricelist};
      this.getXmlPreview()
    },
    // TODO: сделать предупреждение при переходе по вкладкам при неотправленных изменениях
    cancelChanges() {

    },
    saveChanges() {

    },
    togglePreview() {
      this.preview = !this.preview;
      if (this.preview) {
        this.$nextTick().then(() => {
          // this.initializeCodeMirror();
          this.getXmlPreview();
        });
      }
    },
    previewXml(method, data = {}) {
      this.previewType = method;
      this.previewData = data;
      if (this.preview) {
        this.getXmlPreview();
      }
    },
    getXmlPreview() {
      this.code = '<!-- Загружается XML элемент ' + this.previewType + ' -->';
      api.post('xml/preview', {id: this.pricelist.id, method: this.previewType, data: this.previewData})
          .then(({data}) => {
            this.code = data.message;
            this.debug = data.object;
          })
          .catch(error => console.log(error));
    },
    loadPricelist() {
      api.post('pricelists/get', {id: this.id})
          .then(({data}) => {
            this.pricelist = data.object;
          })
          .catch(error => {
            // TODO: тут может уведомление всплывающее сделать и возвращать в общий список
            console.error(error.message);
            setTimeout(() => this.$router.push({name: 'pricelists'}), 3000);
          })
    },
  },
  mounted() {
    this.id = parseInt(this.$route.params.id);
    this.loadPricelist();
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
.yandexmarket-pricelist-tabs {
  position: relative;
  margin-bottom: 0;
  z-index: 1;
}

.yandexmarket-pricelist-tabs >>> .v-tab {
  text-transform: none !important;
}

.yandexmarket-pricelist-tabs >>> .v-tab.v-tab--active {
  background-color: #fff;
  box-shadow: 0 3px 1px -2px rgba(0, 0, 0, 0.2), 0 1px 5px 0 rgba(0, 0, 0, 0.12);
}

.yandexmarket-pricelist-tabs >>> .v-tab.v-tab--active::after {
  content: '';
  display: block;
  background: #fff;
  position: absolute;
  bottom: -5px;
  left: 0;
  right: 0;
  height: 5px;
}

.yandexmarket-pricelist-tabs >>> .v-slide-group__wrapper {
  overflow: visible;
  contain: none;
}

.yandexmarket-pricelist-tabs >>> .v-tabs-slider-wrapper {
  bottom: unset;
  top: 0;
}

.yandexmarket-pricelist-card {
  border-top-left-radius: 0;
}

.yandexmarket-xml-preview >>> .CodeMirror {
  height: auto;
}
</style>