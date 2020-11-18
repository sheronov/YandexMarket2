<template>
  <v-container>
    <v-row>
      <v-col>
        <v-treeview
            v-model="tree"
            :load-children="fetch"
            :items="items"
            selection-type="independent"
            open-on-click
            selectable
            return-object
        ></v-treeview>
      </v-col>
      <v-divider vertical></v-divider>
      <v-col
          cols="12"
          md="6"
      >
        <v-card-text>
          <div
              v-if="tree.length === 0"
              class="title font-weight-light grey--text pa-4 text-center"
          >
            Select your favorite breweries
          </div>

          <v-scroll-x-transition
              group
              hide-on-leave
          >
            <v-chip
                v-for="(selection, i) in tree"
                :key="i"
                color="grey"
                dark
                small
                class="ma-1"
            >
              <v-icon
                  left
                  small
              >
                mdi-beer
              </v-icon>
              {{ selection.name }}
            </v-chip>
          </v-scroll-x-transition>
        </v-card-text>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'Tree',
  data: () => ({
    breweries: [],
    isLoading: false,
    tree: [],
    types: [],
  }),

  computed: {
    items () {
      const children = this.types.map(type => ({
        id: type,
        name: this.getName(type),
        children: this.getChildren(type),
      }))

      return [{
        id: 1,
        name: 'All Breweries',
        children,
      }]
    },
    shouldShowTree () {
      return this.breweries.length > 0 && !this.isLoading
    },
  },

  watch: {
    breweries (val) {
      this.types = val.reduce((acc, cur) => {
        const type = cur.brewery_type

        if (!acc.includes(type)) acc.push(type)

        return acc
      }, []).sort()
    },
  },

  methods: {
    fetch () {
      if (this.breweries.length) return

      return fetch('https://api.openbrewerydb.org/breweries')
          .then(res => res.json())
          .then(data => (this.breweries = data))
          .catch(err => console.log(err))
    },
    getChildren (type) {
      const breweries = []

      for (const brewery of this.breweries) {
        if (brewery.brewery_type !== type) continue

        breweries.push({
          ...brewery,
          name: this.getName(brewery.name),
        })
      }

      return breweries.sort((a, b) => {
        return a.name > b.name ? 1 : -1
      })
    },
    getName (name) {
      return `${name.charAt(0).toUpperCase()}${name.slice(1)}`
    },
  },
}
</script>
