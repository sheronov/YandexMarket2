<template>
  <div class="yandexmarket-pricelist-offers-fields">
    <v-row class="ma-0">
      <v-flex>
        <h4>{{ $t('The main criteria for generating a request') }}</h4>
        <p class="mb-1">{{ $t('When changes are made, an offer preview is updated') }}</p>
      </v-flex>
      <v-spacer/>
      <v-btn @click="previewXml" icon :title="$t('Change the offer in the preview')">
        <v-icon>icon-refresh</v-icon>
      </v-btn>
    </v-row>
    <div class="yandexmarket-pricelist-class">
      <v-text-field
          v-model="classKey"
          :readonly="classKeyDisabled"
          :placeholder="$t('Class for fetching offers (class_key from resource table)')"
          :hint="$t('Be careful with the setting. For simple resources, specify modDocument')"
          :background-color="classKeyDisabled ? 'grey lighten-3' : 'grey lighten-5'"
          dense
          solo
      >
        <template v-slot:prepend-inner>
          <div class="text-no-wrap mr-1 ml-n1">
            <v-icon color="inherit" class="mr-1" :title="$t('Attribute')">icon-tags</v-icon>
            <code class="py-1 d-inline-block">{{ $t('Resource class') }}</code>
          </div>
        </template>
        <template v-slot:append>
          <v-btn small v-if="classKeyDisabled" icon :title="$t('Enable class_key editing')"
                 @click.stop="classKeyDisabled = !classKeyDisabled" class="mt-0 mr-n2">
            <v-icon>icon-pencil</v-icon>
          </v-btn>
          <template v-else>
            <v-btn small icon color="orange" @click.stop="cancelClass" :title="$t('Cancel changes')" class="mt-0 mr-2">
              <v-icon>icon-rotate-left</v-icon>
            </v-btn>
            <v-btn small icon @click.stop="saveClass" :title="$t('Save changes')" class="mt-0 mr-n2"
                   :disabled="classKey === pricelist.class" color="secondary">
              <v-icon>icon-save</v-icon>
            </v-btn>
          </template>
        </template>
      </v-text-field>
    </div>
    <div class="yandexmarket-pricelist-where mb-5 mt-0">
      <v-row class="ma-0 align-center">
        <h4>{{ $t('Conditions for goods') }}</h4>
        <v-tooltip bottom :max-width="400" :close-delay="200" :attach="true">
          <template v-slot:activator="{ on }">
            <v-btn small icon v-on="on" class="ml-1">
              <v-icon>
                icon-question-circle
              </v-icon>
            </v-btn>
          </template>
          <div class="text-caption" style="white-space: pre-line;">{{
              $t('All product fields are available, including TV fields, ms2 options.')
            }} {{ $t('The component will automatically join the columns specified.') }} {{
              $t('If you need to add third-party components - contact support')
            }}
          </div>
        </v-tooltip>
        <v-spacer/>
        <template v-if="editedWhere">
          <v-btn @click="resetWhere" small icon :title="$t('Cancel changes')" class="ml-1" color="orange darken-1">
            <v-icon>icon-rotate-left</v-icon>
          </v-btn>
          <v-btn @click="saveWhere" :disabled="hasErrors"
                 small :title="$t('Save changes')" class="ml-2" color="secondary" height="24">
            <v-icon left>icon-save</v-icon>
            {{ $t('Save') }}
          </v-btn>
        </template>
      </v-row>
      <pricelist-conditions :conditions="pricelist.conditions" :pricelist="pricelist" v-on="$listeners"/>
    </div>

    <v-row class="ma-0">
      <v-flex>
        <h4>{{ $t('Set up offers fields') }}</h4>
        <p class="mb-2">{{ $t('Interactive mode for adding and editing fields') }}</p>
      </v-flex>
      <v-spacer/>
    </v-row>
    <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers" v-if="offerField">
      <pricelist-offer-field
          :item="offerField"
          :fields="pricelist.fields"
          :attributes="pricelist.attributes"
          :lighten="3"
          :available-fields="availableFields('offer',pricelist)"
          :available-types="availableTypes('offer',pricelist)"
          v-on="$listeners"
      />
    </v-expansion-panels>
    <v-alert v-else type="warning" dense border="left">
      {{ $t('Element with type "offer" not found. The "offers" element may have been removed.') }}
      {{ $t('Add an offers element or recreate the pricelist.') }}
    </v-alert>
  </div>
</template>

<script>
import PricelistOfferField from "@/components/PricelistField";
import {mapGetters} from "vuex";
import PricelistConditions from "@/components/PricelistConditions";

export default {
  name: 'PriceListOffer',
  components: {
    PricelistConditions,
    PricelistOfferField,
  },
  props: {
    pricelist: {type: Object, required: true}
  },
  data: () => ({
    openedFields: [0],
    where: '',
    classKey: '',
    classKeyDisabled: true,
    hasErrors: true,
    cmOptions: {
      lineNumbers: true,
      mode: 'application/json',
      gutters: ["CodeMirror-lint-markers"],
      lint: false,
      lineWrapping: true
    }
  }),
  watch: {
    'pricelist.where': {
      immediate: true,
      handler: function (where) {
        this.where = where;
      },
    },
    'pricelist.class': {
      immediate: true,
      handler: function (value) {
        this.classKey = value;
        this.classKeyDisabled = true;
      },
    },
  },
  computed: {
    ...mapGetters('marketplace', ['availableFields']),
    ...mapGetters('field', ['availableTypes']),
    offerField() {
      return this.pricelist.fields.find(field => field.type === 6);
    },
    editedWhere() {
      return (this.where || '') !== (this.pricelist.where || '')
    },
  },
  methods: {
    previewXml() {
      this.$emit('preview:xml', 'offers');
    },
    saveClass() {
      this.$emit('pricelist:updated', {class: this.classKey});
    },
    cancelClass() {
      this.classKey = this.pricelist.class;
      this.classKeyDisabled = true;
    },
    saveWhere() {
      this.$emit('pricelist:updated', {where: this.where ? this.where : null});
    },
    resetWhere() {
      this.where = this.pricelist.where || ''
    }
  },
  mounted() {
    this.previewXml();
    this.openedFields = [0];
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-pricelist-class {
  position: relative;
}

.yandexmarket-pricelist-where .CodeMirror {
  height: auto;
  min-height: 60px;
  z-index: 0;
}

.yandexmarket-pricelist-where .CodeMirror pre.CodeMirror-placeholder {
  color: #999;
}
</style>
