<template>
  <v-row>
    <v-col cols="12" md="6">
      <h4>Основные параметры выгрузки прайс-листа</h4>
      <p class="mb-2">Все поля обязательны к заполнению</p>
      <v-select
          filled
          label="Тип прайс-листа (нельзя изменить)"
          dense
          disabled
          v-model="data.type"
          :items="marketplaces"
      ></v-select>
      <v-text-field
          filled
          label="Название прайс-листа"
          dense
          v-model="data.name"
      ></v-text-field>
      <v-text-field
          filled
          dense
          label="Название файла (укажите вместе с расширением xml)"
          :hint="`Файл будет сохранён в директорию ${pricelist.path}`"
          v-model="data.file"
      ></v-text-field>
      <v-select
          filled
          dense
          class="mb-2"
          label="Режим формирования файла"
          v-model="data.generate_mode"
          :items="modes"
          :hint="modeHint"
          :persistent-hint="!!modeHint"
          :menu-props="{offsetY: true}"
          :attach="true"
      ></v-select>
      <v-text-field
          filled
          :min="0"
          dense
          label="Обновлять файл каждые N минут (если не было изменений)"
          hint="0 - без дополнительных обновлений. Чтобы обновлять файл раз в сутки укажите 1440"
          v-model="data.generate_interval"
          type="number"
      ></v-text-field>
      <v-checkbox v-model="data.active" label="Прайс-лист активен" hide-details dense class="mt-0"></v-checkbox>
      <v-card-actions class="px-0 align-end">
        <v-btn small v-if="hasChanges" @click="cancelChanges" title="Отменить все изменения">
          <v-icon left>icon-undo</v-icon>
          Отменить изменения
        </v-btn>
        <v-spacer/>
        <v-btn :disabled="!hasChanges" @click="saveChanges" color="secondary">
          <v-icon left>icon-save</v-icon>
          Сохранить
        </v-btn>
      </v-card-actions>
    </v-col>
    <v-col cols="12" md="6">
      <v-alert v-if="pricelist.need_generate" type="warning">
        Изменились товары. Файл нужно перегенерировать!
      </v-alert>
      <v-alert v-if="pricelist.generated_on" type="info">
        Предыдущий прайс-лист сформирован {{ generatedOn }}
      </v-alert>
      <p v-if="pricelist.generated_on">Ссылка на файл:
        <a :href="pricelist.fileUrl" title="Файл откроется в новом окне" target="_blank">{{pricelist.fileUrl }}</a>
      </p>
      <h4 v-else class="mb-3">Прайс-лист ещё ни разу не был сформирован</h4>
      <v-btn @click="generateFile" :disabled="loading" color="secondary" class="mb-3"
             title="Существующий файл будет перезаписан">
        Сформировать новый файл
      </v-btn>
      <pre class="text-pre-wrap">{{ log }}</pre>
    </v-col>
  </v-row>
</template>

<script>
import {mapState} from 'vuex';
import api from "@/api";

export default {
  name: 'PriceListGenerate',
  props: {
    pricelist: {required: true, type: Object}
  },
  data: () => ({
    data: {},
    loading: false,
    log: 'Здесь появится лог генерации файла',
    modes: [
      {
        value: 0,
        text: 'Только вручную в админ-панели',
        hint: 'Поменяйте после завершения всех настроек'
      },
      {
        value: 1,
        text: 'При изменении товаров на лету (для небольших сайтов)',
        hint: 'Прайс-лист будет формироваться сразу'
      },
      {
        value: 2,
        text: 'По cron при изменении товаров (рекомендуется)',
        hint: 'Поставьте ежеминутную крон-задачу на /core/components/yandexmarket/cron.php'
      },
      // {
      //   value: 3,
      //   text: 'По cron каждые N минут (независимо от изменений товаров)',
      //   hint: 'Поставьте ежеминутную крон-задачу на /core/components/yandexmarket/cron.php'
      // },
    ],
  }),
  watch: {
    pricelist: {
      immediate: true,
      // eslint-disable-next-line no-unused-vars
      handler: function ({fields, categories, attributes, ...data}) {
        this.data = {...this.data, ...data};
      }
    }
  },
  computed: {
    ...mapState('marketplace', ['marketplaces']),
    generatedOn() {
      return this.pricelist.generated_on.date.replace('.000000', '');
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
      let {fields, categories, attributes, ...data} = this.pricelist;
      return JSON.stringify(this.data) !== JSON.stringify(data);
    }
  },
  methods: {
    cancelChanges() {
      // eslint-disable-next-line no-unused-vars
      let {fields, categories, attributes, ...data} = this.pricelist;
      this.data = {...this.data, ...data};
    },
    saveChanges() {
      this.$emit('pricelist:updated', {...this.data});
    },
    generateFile() {
      if (!this.pricelist.generated_on || confirm('Вы действительно хотите перегенерировать файл? Старый файл будет перезаписан')) {
        this.loading = true;
        api.post('xml/generate', {id: this.pricelist.id})
            .then(({data}) => {
              console.log(data);
              this.log = data.message;
              this.$emit('pricelist:updated', {...data.object}, false);
            })
            .catch(error => console.log(error))
            .then(() => this.loading = false);
      }
    }
  }
}
</script>