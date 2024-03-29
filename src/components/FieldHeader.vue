<template>
  <component :is="readonly ? 'v-sheet' : 'v-expansion-panel-header'"
             class="pr-2 pb-1 v-expansion-panel-header"
             :color="color">
    <template v-slot:actions>
      <v-icon class="ml-3 mr-1 mt-n1">icon-chevron-down</v-icon>
    </template>
    <!--  <v-sheet :color="color" class="v-expansion-panel-header pr-2 pb-1">-->
    <!--    <v-expansion-panel-header :color="color" hide-actions class="pr-2 pb-1">-->
    <inline-edit-dialog v-if="!isSingle(field)">
      <v-btn icon small :title="$t('Sequence number (click to change)')" class="ml-n2">
        #{{ field.rank }}
      </v-btn>
      <template v-slot:input>
        <v-text-field
            :value="field.rank"
            @input="field.rank = parseInt($event || '0')"
            :label="$t('Priority')"
            single-line
            type="number"
            prepend-icon="icon-sort"
            min="0"
        />
      </template>
    </inline-edit-dialog>
    <span style="padding-left: 1px; padding-right: 4px;">
         &lt;{{ item.name || $t('enter element') }}&gt;
    </span>
    <span class="pl-1 grey--text">
      <span v-if="item.label.replace(' *','') !== item.name">{{ item.label.replace(' *', '') }}</span>
      <small v-if="!item.id"> {{ $t('(select type)') }}</small>
    </span>
    <v-tooltip v-if="item.help" bottom :max-width="400" :close-delay="200" :attach="true">
      <template v-slot:activator="{ on }">
        <v-btn small icon v-on="on" class="ml-1">
          <v-icon>
            icon-question-circle
          </v-icon>
        </v-btn>
      </template>
      <div class="text-caption" style="white-space: pre-line;">{{ item.help }}</div>
    </v-tooltip>
    <v-spacer/>
    <template v-if="edited">
      <v-btn @click.stop="$emit('edit:cancel')" small icon :title="$t('Cancel changes')" class="ml-1"
             color="orange darken-1">
        <v-icon>icon-rotate-left</v-icon>
      </v-btn>
      <v-btn @click.stop="saveField" small :title="$t('Save changes')" class="ml-2 mb-1" color="secondary" height="26">
        <v-icon left>icon-save</v-icon>
        {{ $t('Save') }}
      </v-btn>
    </template>
    <template v-else>
      <v-btn
          v-if="field.id"
          small depressed
          @click="$emit('attribute:add',$event)"
          :title="$t(disabledAddAttribute ? 'Save new first' : 'Add attribute')"
          color="transparent"
          min-width="40"
          class="px-0"
          :disabled="disabledAddAttribute"
      >
        <v-icon class="icon-xs mr-1" color="grey darken-1">icon-plus</v-icon>
        <v-icon class="icon-xs" color="grey darken-1">icon-font</v-icon>
      </v-btn>
      <v-btn v-if="field.id"
             small icon
             :title="$t('Edit field name and type')"
             @click="$emit('edit:toggle',$event)"
             :color="edit ? 'secondary': 'default'"
             class="ml-1"
      >
        <v-icon>icon-pencil</v-icon>
      </v-btn>
      <v-btn small icon
             :title="$t('Delete field')"
             @click.stop="deleteField"
             v-if="!isSingle(field)"
             class="ml-1">
        <v-icon>icon-trash</v-icon>
      </v-btn>
    </template>
    <!--    </v-expansion-panel-header>-->
    <!--  &lt;!&ndash;  </v-sheet>-->
  </component>
</template>

<script>
import {mapGetters} from 'vuex';
import InlineEditDialog from "@/components/InlineEditDialog";
import api from '@/api';

import VSheet from "vuetify/lib/components/VSheet/VSheet";
import VExpansionPanelHeader from "vuetify/lib/components/VExpansionPanel/VExpansionPanelHeader";

export default {
  name: 'FieldHeader',
  props: {
    field: {required: true, type: Object},
    item: {required: true, type: Object},
    color: {type: String, default: `grey lighten-3`},
    edit: {type: Boolean, default: false},
    disabledAddAttribute: {type: Boolean, default: false},
    readonly: {type: Boolean, default: true}
  },
  components: {
    InlineEditDialog,
    VSheet,
    VExpansionPanelHeader
  },
  computed: {
    ...mapGetters('field', [
      'isRoot',
      'isSingle',
      'typeText',
      'isUnique',
      'isSimpleString'
    ]),
    edited() {
      return JSON.stringify(this.item) !== JSON.stringify(this.field);
    },
  },
  methods: {
    deleteField() {
      if (!this.field.id) {
        this.$emit('field:deleted', this.field);
        return;
      }
      if (confirm(this.$t('Are you sure you want to delete the {name} field?', {name: this.field.name} ))) {
        api.post('Fields/Remove', {id: this.field.id})
            .then(() => this.$emit('field:deleted', this.field))
            .catch(error => console.log(error));
      }
    },
    saveField(event) {
      setTimeout(() => {
        api.post(!this.field.id ? 'Fields/Create' : 'Fields/Update', this.field)
            .then(({data}) => {
              this.$emit('edit:toggle', event, false);
              this.$nextTick().then(() => this.$emit('field:updated', data.object));
            })
            .catch(error => console.error(error));
      }, 10);
    },
  }
}
</script>
