<template>
  <div class="yandexmarket-pricelist">
    <v-tabs v-if="id" class="yandexmarket-pricelist-tabs pr-15" background-color="transparent" :height="40">
      <v-tab :to="{name: 'pricelist', params: {id: id}}" :ripple="false" exact>
        Настройки магазина
      </v-tab>
      <v-tab :to="{name: 'pricelist.categories', params: {id: id}}" :ripple="false" exact>
        Настройки категорий
      </v-tab>
      <v-tab v-if="hasOffers" :to="{name: 'pricelist.offers', params: {id: id}}" :ripple="false" exact>
        Настройки предложений
      </v-tab>
      <v-tab :to="{name: 'pricelist.generate', params: {id: id}}" :ripple="false" exact>
        Выгрузка и параметры
      </v-tab>
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
                @field:created="fieldUpdated($event, true)"
                @field:updated="fieldUpdated"
                @field:deleted="fieldDeleted"
                @attribute:created="attributeUpdated($event, true)"
                @attribute:updated="attributeUpdated"
                @attribute:deleted="attributeDeleted"
                @condition:created="conditionUpdated($event, true)"
                @condition:updated="conditionUpdated"
                @condition:deleted="conditionDeleted"
                @category:added="categoryAdded"
                @category:removed="categoryRemoved"
                @pricelist:updated="pricelistUpdated"
            ></router-view>
            <v-card-actions class="px-0" v-if="false">
              <v-btn v-if="!hasChanges" :to="{name: 'pricelists'}" exact title="Ко всем прайс-листам">
                <v-icon left>icon-arrow-left</v-icon>
                Вернуться
              </v-btn>
              <v-btn v-else title="Отменить все изменения">
                <v-icon left>icon-undo</v-icon>
                Отменить
              </v-btn>
              <v-spacer/>
              <v-btn :disabled="!hasChanges" color="secondary">
                <v-icon left>icon-save</v-icon>
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-col>
          <v-col cols="12" md="5" v-if="showPreview && preview">
            <div class="yandexmarket-xml-preview">
              <h4><label for="yandexmarket-preview">Предпросмотр XML элемента &lt;{{ previewType }}&gt;</label></h4>
              <p class="mb-2">Автоматически обновляется при любом изменении</p>
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
import 'codemirror/mode/javascript/javascript';
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
    previewLoading: false,
    previewType: null,
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
    },
    hasCategories() {
      return this.pricelist && this.pricelist.fields.find(field => [4,14].indexOf(field.type) !== -1);
    },
    hasOffers() {
      return this.pricelist && this.pricelist.fields.find(field => [5,15].indexOf(field.type) !== -1);
    }
  },
  methods: {
    categoryAdded(resourceId, send = true) {
      if (this.pricelist.categories.indexOf(parseInt(resourceId)) === -1) {
        this.pricelist.categories.push(parseInt(resourceId));
      }
      if (send) {
        api.post('Categories/Create', {pricelist_id: this.pricelist.id, resource_id: resourceId})
            .then(() => this.getXmlPreview())
            .catch(error => {
              console.error(error);
              this.categoryRemoved(resourceId, false)
            })
        this.pricelistUpdated({need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)}, false);
      }
    },
    categoryRemoved(resourceId, send = true) {
      this.pricelist.categories = this.pricelist.categories.filter(selected => selected !== resourceId);
      if (send) {
        api.post('Categories/Remove', {pricelist_id: this.pricelist.id, resource_id: resourceId})
            .then(() => this.getXmlPreview())
            .catch(error => console.error(error)); // можно добавлять назад, если по какой-то причине не удалился
        this.pricelistUpdated({need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)}, false);
      }
    },
    fieldUpdated(field, created = false) {
      let fields = this.pricelist.fields.slice();
      if (created) {
        fields.push(field);
      } else {
        fields.splice(fields.findIndex(item => item.id ? item.id === field.id : item.parent === field.parent), 1, field);
      }

      if(field.need_reload) {
        this.loadPricelist();
      }  else {
        this.pricelistUpdated({
          fields,
          need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
        }, false);
      }
    },
    fieldDeleted(field) {
      let fields = this.pricelist.fields.slice();
      fields.splice(fields.findIndex(item => item.id === field.id), 1);
      this.pricelistUpdated({
        fields: fields,
        need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
      }, false);
    },
    attributeUpdated(attr, created = false) {
      let attributes = this.pricelist.attributes.slice();
      if (created) {
        attributes.push(attr);
      } else {
        attributes.splice(attributes.findIndex(item => item.id ? item.id === attr.id : item.field_id === attr.field_id), 1, attr);
      }
      this.pricelistUpdated({
        attributes,
        need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
      }, false);
    },
    attributeDeleted(attribute) {
      let attributes = this.pricelist.attributes.slice();
      attributes.splice(attributes.findIndex(item => item.id === attribute.id), 1);
      this.pricelistUpdated({
        attributes,
        need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
      }, false);
    },
    conditionUpdated(condition, created = false) {
      let conditions = this.pricelist.conditions.slice();
      if (created) {
        conditions.push(condition);
      } else {
        conditions.splice(conditions.findIndex(item => item.id ? item.id === condition.id : !item.id), 1, condition);
      }
      this.pricelistUpdated({
        conditions,
        need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
      }, false);
    },
    conditionDeleted(condition) {
      let conditions = this.pricelist.conditions.slice();
      conditions.splice(conditions.findIndex(item => item.id === condition.id), 1);
      this.pricelistUpdated({
        conditions,
        need_generate: this.pricelist.need_generate || (this.pricelist.active && this.pricelist.generated_on)
      }, false);
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
    previewXml(method) {
      this.previewType = method;
      if (this.preview) {
        this.getXmlPreview();
      }
    },
    getXmlPreview() {
      if (this.previewLoading) {
        return;
      }
      this.previewLoading = true;
      this.code = '<!-- Загружается XML элемент ' + this.previewType + ' -->';
      api.post('Xml/Preview', {id: this.pricelist.id, method: this.previewType})
          .then(({data}) => {
            this.code = data.message;
            this.debug = data.object;
          })
          .catch(error => console.log(error))
          .then(() => this.previewLoading = false);
    },
    loadPricelist() {
      api.post('Pricelists/Get', {id: this.id})
          .then(({data}) => {
            this.pricelist = data.object;
            let route = this.$route.matched.find(r => r.meta && r.meta.replaceable);
            if (route) {
              this.$set(route, 'meta', {...route.meta, title: `${data.object.name} (${data.object.id})`});
            }
          })
          .catch(error => {
            console.error(error);
            setTimeout(() => this.$router.push({name: 'pricelists'}), 3000);
          })
    },
    pricelistUpdated(pricelist, save = true) {
      this.pricelist = {
        ...this.pricelist,
        ...pricelist,
      };
      if (save) {
        this.code = '<!-- Загружается XML элемент ' + this.previewType + ' -->';
        // eslint-disable-next-line no-unused-vars
        let {fields, categories, attributes, conditions, ...pricelist} = this.pricelist;
        api.post('Pricelists/Update', {...pricelist})
            .then(({data}) => {
              this.pricelist = {...this.pricelist, ...data.object};
              this.getXmlPreview()
            })
            .catch(error => console.error(error));
      } else if (this.showPreview) {
        this.getXmlPreview()
      }
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


.yandexmarket-xml-preview {
  position: relative;
  z-index: 0;
}

.yandexmarket-xml-preview >>> .CodeMirror {
  height: auto;
}
</style>
