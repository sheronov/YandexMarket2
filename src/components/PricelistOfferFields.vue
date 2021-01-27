<template>
  <div class="yandexmarket-offer-field pl-4">
    <v-card outlined style="margin-top: -1px;" tile >
      <!--      <fieldset class="yandexmarket-field-fieldset">-->
      <!--        <legend>-->
      <!--          <v-combobox-->
      <!--              :value="tag"-->
      <!--              :items="items"-->
      <!--              @input="changedTag"-->
      <!--              class="flex-grow-0 yandexmarket-offer-field-tag text-center"-->
      <!--              label="Укажите элемент"-->
      <!--              placeholder="Введите или выберите из списка"-->
      <!--              background-color="grey lighten-3"-->
      <!--              hide-details-->
      <!--              solo-->
      <!--              flat-->
      <!--              dense-->
      <!--          >-->
      <!--            <template v-slot:prepend-inner>-->
      <!--              <v-icon>icon-angle-left</v-icon>-->
      <!--            </template>-->
      <!--            <template v-slot:append>-->
      <!--              <v-icon>icon-angle-right</v-icon>-->
      <!--            </template>-->
      <!--          </v-combobox>-->
      <!--        </legend>-->
      <v-card-title class="pb-0 pl-5">
        <offer-field-attributes v-if="field.attributes" :attributes="field.attributes"/>
      </v-card-title>
      <v-card-title class="pl-2 pt-0">
        <v-btn icon title="Добавить атрибуты" @click="addAttribute" class="mr-2" :color="field.attributes ? 'primary' : ''">
          <v-icon>icon-font</v-icon>
        </v-btn>
        <v-combobox
            :value="tag"
            :items="items"
            @input="changedTag"
            class="flex-grow-0 yandexmarket-offer-field-tag text-center mr-2"
            label="Укажите элемент"
            placeholder="Введите или выберите из списка"
            background-color="grey lighten-3"
            hide-details
            solo
            flat
            dense
        >
          <template v-slot:prepend-inner>
            <v-icon>icon-angle-left</v-icon>
          </template>
          <template v-slot:append>
            <v-icon>icon-angle-right</v-icon>
          </template>
        </v-combobox>
        <v-select
            :value="0"
            :items="types"
            @input="changedType"
            class="yandexmarket-offer-field-type flex-grow-0 mr-3"
            :full-width="false"
            label="Тип элемента"
            placeholder="Выберите тип"
            background-color="grey lighten-4"
            :menu-props="{offsetY: true}"
            hide-details
            solo
            flat
            dense
        >
          <template v-slot:prepend-inner>
            <div class="text-no-wrap">

              <span>Тип:</span>
            </div>
          </template>
        </v-select>
        <v-combobox
            label="Выберите поле товара"
            placeholder='Или введите в формате "modResource.field"'
            hide-details
            solo
            :items="['modResource.pagetitle']"
            dense
            :value="value"
        >
        </v-combobox>
        <v-btn
            title="Ввести код (вместо выбора столбца)"
            color="grey lighten-3"
            @click="toggleCode"
            class="ml-3"
            min-width="30"
            elevation="0">
          <v-icon>icon-code</v-icon>
        </v-btn>

      </v-card-title>
<!--   TODO: сделать здесь expansion panel   -->
      <v-card-text class="pl-0">
        <div v-if="field.fields" class="yandexmarket-offer-field-children">
          <pricelist-offer-fields
              v-for="(child,childTag) in field.fields"
              :key="childTag"
              :field="child"
              :tag="childTag"
              :values="values"/>
        </div>
        <v-btn bottom left fab absolute small :title="`Добавить поле в узел <${tag}>`">
          <v-icon>icon-plus</v-icon>
        </v-btn>
      </v-card-text>
      <!--      </fieldset>-->
    </v-card>
  </div>
</template>

<script>
import OfferFieldAttributes from "@/components/OfferFieldAttributes";

export default {
  name: 'PricelistOfferFields',
  components: {OfferFieldAttributes},
  props: {
    values: {required: true, type: [Object, Array]},
    field: {required: true, type: Object},
    tag: {required: true, type: String}
  },
  data: () => ({
    hovered: false,
    code: false
  }),
  computed: {
    value() {
      let value = this.values[this.tag];
      if (Array.isArray(value)) {
        value = null;
        // значит параметр множественный, скорее всего param
      } else if (value instanceof Object) {
        //значит есть атрибуты
        value = value.column || value.handler || null;
      }
      return value;
    },
    items() {
      return [
        this.tag,
        'test',
        'offer'
      ]
    },
    types() {
      return [
        {value: 0, text: 'родитель'},
        {value: 1, text: 'текстовый'},
      ]
    }
  },
  methods: {
    hover() {
      this.hovered = true;
    },
    unhover() {
      this.hovered = false;
    },
    addField() {

    },
    addAttribute() {

    },
    changedTag() {

    },
    changedType() {

    },
    toggleCode() {
      this.code = !this.code;
    }
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style>
.yandexmarket-offer-field {
  position: relative;
}

.yandexmarket-field-fieldset {
  border: 1px solid #ddd;
}

.yandexmarket-field-fieldset {
  padding: 10px;
  margin-bottom: 10px;
  margin-top: 10px;
}

.yandexmarket-offer-field-type {
  max-width: 200px !important;
  font-size: 0.875em !important;
}

.yandexmarket-offer-field-tag {
  max-width: 180px !important;
}

.yandexmarket-offer-field-tag .v-select__slot input {
  /*display: none;*/
}

.v-select.v-select--is-menu-active .v-input__append-inner .icon {
  transform: rotate(90deg);
}

.yandexmarket-offer-field-tag .v-select__slot input {
  text-align: center;
}
</style>