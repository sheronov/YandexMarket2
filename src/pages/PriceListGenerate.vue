<template>
  <v-row>
    <v-col cols="12" md="6">
      <p>Название прайс-листа {{ pricelist.name }}</p>
      <p>Тип прайс-листа (не поменять): {{ pricelist.type }}</p>
      <p>Название файла: {{ pricelist.file }} (сохраняется в pricelist.path - вывести)</p>
      <p>Активный: {{ pricelist.active }}</p>
      <p>Режим генерации файла {{ pricelist.generate_mode }}</p>
      <p>Предыдущий файл сформирован в {{ pricelist.generated_on }}</p>
      <p>Частота обновлений (0, только при изменениях): {{ pricelist.generate_interval }}</p>
      <p>Нужно ли перегенерировать: {{ pricelist.need_generate }}</p>
    </v-col>
    <v-col cols="12" md="6">
      <p>Кнопка сформировать новый файл</p>
      <p>Скачать предыдущий сформированный файл</p>
      <p>Лог выгрузки</p>
    </v-col>
  </v-row>
</template>

<script>
export default {
  props: {
    pricelist: {required: true, type: Object}
  }
}
</script>