<template>
  <v-col :sm="edit ? 12 : 6" cols="12" class="yandexmarket-field-attribute pt-0">
    <div class="grey--text" style="font-size: 12px;" v-if="label">{{ label }}</div>
    <component
        v-if="!edit"
        :is="item.values ? 'v-select' : 'v-text-field'"
        v-model="item.value"
        :items="item.values"
        :placeholder="$t(item.values ? 'Select from the list' : 'Enter value')"
        :hint="item.handler ? '</> '+item.handler : null"
        :persistent-hint="!!item.handler"
        :hide-details="!item.handler"
        :attach="true"
        :menu-props="{offsetY: true}"
        class="yandexmarket-field-attribute-input"
        dense
        solo
    >
      <template v-slot:prepend-inner>
        <div class="text-no-wrap mr-1 ml-n1">
          <v-icon color="inherit" class="mr-1" :title="$t('Attribute')">icon-font</v-icon>
          <code style="position:relative; top:1px;">{{ item.name }}</code>
        </div>
      </template>
      <template v-slot:append>
        <v-btn small v-if="!edited" icon :title="$t('Edit attribute properties')" @click.stop="editAttr"
               class="mt-n1 mr-n2">
          <v-icon>icon-pencil</v-icon>
        </v-btn>
        <v-btn v-else small icon @click.stop="saveChanges" :title="$t('Save changes')" class="mt-1"
               color="secondary">
          <v-icon>icon-save</v-icon>
        </v-btn>
      </template>
      <template v-slot:append-outer v-if="edited">
        <v-btn fab x-small absolute depressed width="24" height="24" style="top:-5px;right:0" color="grey lighten-4">
          <v-icon size="14" @click="cancelChanges" :title="$t('Cancel changes')" color="orange">icon-rotate-left
          </v-icon>
        </v-btn>
      </template>
    </component>
    <v-sheet v-else elevation="1" class="pt-1 px-2" color="grey lighten-5">
      <v-row class="ma-0">
        <div v-if="attribute.id">{{ $t('Editing the {name} attribute', {name: attribute.name}) }}</div>
        <div v-else>{{ $t('Adding a new attribute') }}
          <v-tooltip bottom :max-width="400" :close-delay="200" :attach="true">
            <template v-slot:activator="{ on }">
              <v-btn small icon v-on="on" class="mt-n1">
                <v-icon>icon-question-circle</v-icon>
              </v-btn>
            </template>
            <div class="text-caption" style="white-space: pre-line;">{{
                $t('Attribute description can be specified through lexicons')
              }}
            </div>
          </v-tooltip>
        </div>
        <v-spacer/>
        <template v-if="edited">
          <v-btn @click="cancelChanges" small icon :title="$t('Cancel changes')" class="ml-1" color="orange darken-1">
            <v-icon>icon-rotate-left</v-icon>
          </v-btn>
          <v-btn @click="saveChanges" small :title="$t('Save changes')" class="ml-2 mb-1" color="secondary" height="26">
            <v-icon left>icon-save</v-icon>
            {{ $t('Save') }}
          </v-btn>
        </template>
        <template v-else>
          <v-btn v-if="item.id" small icon :title="$t('Cancel')" @click="editAttr" class="ml-1"
                 color="secondary">
            <v-icon>icon-pencil</v-icon>
          </v-btn>
          <v-btn small icon :title="$t('Delete field')" @click="deleteAttribute" class="ml-1">
            <v-icon>icon-trash</v-icon>
          </v-btn>
        </template>
      </v-row>
      <v-row dense class="mt-1">
        <v-col md="4">
          <v-text-field
              v-model="item.name"
              :label="$t('Parameter *')"
              :placeholder="$t('in latin')"
              dense
              required
          ></v-text-field>
        </v-col>
        <v-col md="4">
          <v-select
              v-model="item.type"
              :label="$t('Value type')"
              :items="items"
              dense
              :menu-props="{offsetY: true}"
              :attach="true"
          ></v-select>
        </v-col>
        <v-col md="4">
          <component
              :is="item.values ? 'v-select' : 'v-text-field'"
              :value="item.value"
              @input="item.value = $event"
              :items="item.values"
              :label="$t(item.values ? 'Select from the list' : 'Enter value')"
              :placeholder="$t('Product field')"
              hide-details
              :attach="true"
              :menu-props="{offsetY: true}"
              dense
          >
            <template v-slot:append-outer v-if="item.type !== 0">
              <v-btn
                  :title="$t(openedCode ? 'To close, clear the entered code' : 'Add value handler code')"
                  :color="openedCode ? 'secondary' : 'accent'"
                  @click="toggleCode"
                  small
                  class="px-2 text-center mt-n1"
                  min-width="30"
              >
                <v-icon>icon-code</v-icon>
              </v-btn>
            </template>
          </component>
        </v-col>
      </v-row>
      <vue-codemirror
          v-if="openedCode"
          class="pb-2 mt-n2"
          v-model="item.handler"
          :options="cmOptions"
          :placeholder="`${$t('Example')}: {$input === '${$t('Yes')}' ? true : false}`"
      ></vue-codemirror>
    </v-sheet>
  </v-col>
</template>

<script>
import VTextField from "vuetify/lib/components/VTextField/VTextField";
import VSelect from "vuetify/lib/components/VSelect/VSelect";
import VCombobox from "vuetify/lib/components/VCombobox/VCombobox";
import {codemirror} from 'vue-codemirror';
import api from "@/api";

export default {
  name: 'FieldAttribute',
  components: {
    VSelect,
    VCombobox,
    VTextField,
    VueCodemirror: codemirror
  },
  props: {
    attribute: {required: true, type: [Object]}
  },
  data() {
    return {
      code: false,
      item: {},
      edit: false,
      items: [
        {value: 0, text: this.$t('text (without processing)')},
        {value: 1, text: this.$t('value from column')},
      ],
      cmOptions: {
        taSize: 4,
        mode: {
          name: 'smarty',
          baseMode: 'text/html',
          version: 3,
        },
        line: true,
        lineNumbers: true,
        lineWrapping: true
      },
    }
  },
  watch: {
    attribute: {
      immediate: true,
      handler: function (attribute) {
        this.item = {...this.item, ...attribute};
        this.$nextTick().then(() => this.code = !!this.item.handler)
      }
    }
  },
  computed: {
    edited() {
      return JSON.stringify(this.item) !== JSON.stringify(this.attribute);
    },
    label() {
      let label = '';
      if (this.item.label && this.item.label.replace(' *', '') !== this.item.name) {
        label = this.item.label;
      }
      return label;
    },
    openedCode() {
      return !!this.item.handler || this.code;
    },
  },
  methods: {
    editAttr() {
      this.edit = !this.edit;
    },
    saveChanges() {
      setTimeout(() => {
        api.post(!this.item.id ? 'Attributes/Create' : 'Attributes/Update', this.item)
            .then(({data}) => {
              this.edit = false;
              this.$nextTick().then(() => this.$emit('attribute:updated', data.object));
            })
            .catch(error => console.error(error))
      }, 10);
    },
    cancelChanges() {
      this.item = {...this.item, ...this.attribute};
    },
    deleteAttribute() {
      if (!this.item.id) {
        this.$emit('attribute:deleted', this.item);
        return;
      }
      if (confirm(this.$t('Are you sure you want to remove the {name} attribute?', {name: this.item.name}))) {
        api.post('Attributes/Remove', {id: this.item.id})
            .then(() => this.$emit('attribute:deleted', this.item))
            .catch(error => console.log(error));
      }
    },
    toggleCode() {
      if (this.item.handler) {
        return;
      }
      this.code = !this.code;
    },
  },
  mounted() {
    if (!this.attribute.id) {
      this.edit = true;
    }
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-field-attribute, .yandexmarket-field-attribute-input {
  position: relative;
}

.yandexmarket-field-attribute .CodeMirror {
  height: auto;
  min-height: 30px;
}
</style>
