<template>
  <v-row class="yandexmarket-pricelist-generate">
    <v-col cols="12" md="6">
      <h4>{{ $t('Main parameters of pricelist generation') }}</h4>
      <p class="mb-2">{{ $t('All fields are required') }}</p>
      <v-select
          filled
          :label="$t('Pricelist type (set when creating)')"
          dense
          disabled
          v-model="data.type"
          :items="marketplaces"
      ></v-select>
      <v-text-field
          filled
          :label="$t('Pricelist name')"
          dense
          v-model="data.name"
      ></v-text-field>
      <v-text-field
          filled
          dense
          class="mb-1"
          :label="$t('Pricelist filename') + ' ' + $t('(specify with xml extension)')"
          :hint="$t('The file will be available at {fileUrl}', {fileUrl: pricelist.fileUrl})"
          v-model="data.file"
      ></v-text-field>
      <v-select
          filled
          dense
          class="mb-2"
          :label="$t('File generation mode')"
          v-model="data.generate_mode"
          :items="modes"
          :hint="modeHint"
          :persistent-hint="!!modeHint"
          :menu-props="{offsetY: true}"
          :attach="true"
      ></v-select>
      <v-text-field
          v-if="data.generate_mode"
          filled
          :min="0"
          dense
          :label="$t('Regenerate the file every N minutes, even if there were no changes')"
          :hint="$t('0 - no additional updates. To update the file once a day, specify 1440')"
          v-model="data.generate_interval"
          type="number"
      ></v-text-field>
      <v-checkbox
          v-model="data.active"
          :label="data.active
          ? $t('Pricelist is active (monitoring changes in settings and related products)')
          : $t('Pricelist is NOT active (activate after setup is complete)')"
          hide-details
          class="mt-0 mb-2"
      />
      <v-card-actions class="px-0">
        <v-btn small v-if="hasChanges" @click="cancelChanges" :title="$t('Revert all changes')">
          <v-icon left>icon-undo</v-icon>
          {{ $t('Cancel changes') }}
        </v-btn>
        <v-spacer/>
        <v-btn :disabled="!hasChanges" @click="saveChanges" color="secondary" small>
          <v-icon left small>icon-save</v-icon>
          {{ $t('Save') }}
        </v-btn>
      </v-card-actions>
      <template v-if="rootField">
        <h4 class="mb-1">{{ $t('File root element settings') }}</h4>
        <v-expansion-panels v-model="openedFields" multiple class="pb-2" key="offers">
          <pricelist-field
              :readonly="false"
              :item="rootField"
              :fields="pricelist.fields"
              :attributes="pricelist.attributes"
              :lighten="3"
              v-on="$listeners"
              :available-fields="[]"
              :available-types="[]"
              parent="root"
          />
        </v-expansion-panels>
      </template>
    </v-col>
    <v-col cols="12" md="6">
      <v-alert v-if="pricelist.need_generate" type="warning" dense>
        {{ $t('Products or settings have changed. The file needs to be regenerated!') }}
      </v-alert>
      <v-alert v-if="pricelist.generated_on" type="info" color="accent" dense>
        {{ $t('Previous pricelist generated in {generatedAt}', {generatedAt: pricelist.generated_on}) }}
      </v-alert>
      <p v-if="pricelist.generated_on">{{ $t('Link to file:') }}
        <a :href="pricelist.fileUrl" :title="$t('The file will open in a new window')" target="_blank">{{ pricelist.fileUrl }}</a>
      </p>
      <h4 v-else class="mb-3">{{ $t('The pricelist has not yet been formed') }}</h4>
      <v-btn @click="generateFile" :disabled="loading" color="secondary" class="mb-3"
             :title="$t('The existing file will be overwritten')">
        {{ $t('Generate a new file') }}
      </v-btn>
      <fieldset class="x-window modx-console zoom-in generation-log pa-2">
        <legend class="px-2">{{ $t('File generation log') }}</legend>
        <div v-html="log" class="modx-console-text px-2"></div>
      </fieldset>
    </v-col>
  </v-row>
</template>

<script>
import {mapState} from 'vuex';
import api from "@/api";
import PricelistField from "@/components/PricelistField";

export default {
  name: 'PriceListGenerate',
  props: {
    pricelist: {required: true, type: Object}
  },
  components: {
    PricelistField
  },
  data() {
    return {
      openedFields: [],
      data: {},
      loading: false,
      log: this.$t('File generation log will appear here'),
      modes: [
        {
          value: 0,
          text: this.$t('Only manually in the admin panel (manual mode)'),
          hint: this.$t('If a cron task is set, then this pricelist will not be included')
        },
        {
          value: 1,
          text: this.$t('When changing products on the fly (for small sites)'),
          hint: this.$t('The pricelist will be formed immediately. Force update by cron')
        },
        {
          value: 2,
          text: this.$t('By cron when changing products (recommended)'),
          hint: this.$t('Install a cron job every minute at /core/components/yandexmarket2/cron/generate.php')
        },
      ],
    }
  },
  watch: {
    pricelist: {
      immediate: true,
      // eslint-disable-next-line no-unused-vars
      handler: function ({fields, categories, attributes, conditions, ...data}) {
        this.data = {...this.data, ...data};
      }
    }
  },
  computed: {
    ...mapState('marketplace', ['marketplaces']),
    rootField() {
      return this.pricelist.fields.find(field => field.type === 1);
    },
    modeHint() {
      let mode = this.modes.find(m => m.value === parseInt(this.data.generate_mode));
      if (mode) {
        return mode.hint;
      }
      return '';
    },
    hasChanges() {
      // eslint-disable-next-line no-unused-vars
      let {fields, categories, attributes, conditions, ...data} = this.pricelist;
      return JSON.stringify(this.data) !== JSON.stringify(data);
    },
    topic() {
      // for logging
      return `/generate-${this.pricelist.id}/`;
    }
  },
  methods: {
    cancelChanges() {
      // eslint-disable-next-line no-unused-vars
      let {fields, categories, attributes, conditions, ...data} = this.pricelist;
      this.data = {...this.data, ...data};
    },
    saveChanges() {
      this.$emit('pricelist:updated', {...this.data});
    },
    getLog() {
      api.post('Xml/Log', {topic: this.topic})
          .then(({data}) => {
            if (data && data.data) {
              this.log += data.data;
            }
            if (!data.complete) {
              setTimeout(this.getLog, 1000);
            }
          });
    },
    generateFile() {
      if (!this.pricelist.generated_on || confirm(this.$t('Are you sure you want to regenerate the file?') + ' ' + this.$t('The existing file will be overwritten'))) {
        this.loading = true;
        this.log = '';
        api.post('Xml/Generate', {id: this.pricelist.id, topic: this.topic, clear: true})
            .then(({data}) => this.$emit('pricelist:updated', {...data.object}, false))
            .catch(error => console.error(error))
            .then(() => this.loading = false);
        setTimeout(this.getLog, 100);
      }
    }
  }
}
</script>

<style>
.yandexmarket-pricelist-generate .generation-log {
  border-color: #bdbdbd;
  box-shadow: none;
  border-width: 1px;
  padding: 8px;
  border-style: solid;
}

.yandexmarket-pricelist-generate .generation-log .modx-console-text {
  line-height: 1.5;
  word-break: break-word;
}

/*noinspection CssUnusedSymbol*/
.yandexmarket-pricelist-generate .generation-log .info {
  background-color: inherit !important;
  border-color: inherit !important;
}

.yandexmarket-pricelist-generate .yandexmarket-textarea-log textarea {
  font-family: monospace;
  color: #999 !important;
  font-size: 0.875em;
  line-height: 1.25rem;
}
</style>
