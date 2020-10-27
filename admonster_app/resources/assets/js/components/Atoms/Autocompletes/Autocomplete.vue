<template>
    <v-autocomplete
        v-model="selected"
        :chips="chips"
        :dense="dense"
        :disabled="disabled"
        :item-text="itemText"
        :item-value="itemValue"
        :items="items"
        :label="label"
        :menu-props="menuProps"
        :multiple="multiple"
        :no-data-text="noDataText"
        :placeholder="placeholder"
    >
        <!-- scoped-slot: selection -->
        <template slot="selection" slot-scope="{ item, index }">
            <template v-if="slotSelectionType === 'image-and-text'">
                <template v-if="index < displayableNumber">
                    <v-chip close @input="remove(item[itemValue])">
                        <v-avatar>
                            <img :src="item[itemAvatar]">
                        </v-avatar>
                        {{ item[itemText] }}
                    </v-chip>
                </template>
                <template v-else-if="index === displayableNumber">
                    <span class="grey--text caption">
                        (+{{ selected.length - displayableNumber }} others)
                    </span>
                </template>
            </template>
            <template v-else>
                <template v-if="index < displayableNumber">
                    <v-chip close @input="remove(item[itemValue])">
                        {{ item[itemText] }}
                    </v-chip>
                </template>
                <template v-else-if="index === displayableNumber">
                    <span class="grey--text caption">
                        (+{{ selected.length - displayableNumber }} others)
                    </span>
                </template>
            </template>
        </template>
        <!-- slot: prepend-item -->
        <template slot="prepend-item">
            <template v-if="slotPrependItemType === 'all'">
                <v-list-tile
                    v-show="items.length > 0"
                    ripple
                    @click="toggleAll()"
                >
                    <v-list-tile-action>
                        <v-icon>{{ allSelectIcon }}</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-content>
                        <v-list-tile-title>ALL</v-list-tile-title>
                    </v-list-tile-content>
                </v-list-tile>
                <v-divider class="ma-0"></v-divider>
            </template>
        </template>
    </v-autocomplete>
</template>

<script>
const DEFAULT_MENU_PROPS = {
    closeOnClick: false,
    closeOnContentClick: false,
    openOnClick: false,
    maxHeight: 300
}

export default {
    props: {
        chips: { type: Boolean, required: false, default: false },
        dense: { type: Boolean, required: false, default: false },
        disabled: { type: Boolean, required: false, default: false },
        displayableNumber: { type: Number, required: false, default: 10 },
        itemAvatar: { type: String, required: false, default: 'avatar' },
        itemText: { type: String, required: false, default: 'text' },
        itemValue: { type: String, required: false, default: 'value' },
        items: { type: Array, required: true },
        label: { type: String, required: false, default: undefined },
        menuProps: { type: Object, required: false, default: () => DEFAULT_MENU_PROPS },
        multiple: { type: Boolean, required: false, default: false },
        noDataText: { type: String, required: false, default: undefined },
        placeholder: { type: String, required: false, default: undefined },
        slotPrependItemType: { type: String, required: false, default: undefined },
        slotSelectionType: { type: String, required: false, default: undefined },
        value: { type: Array, required: true },
    },
    data: () => ({
        selected: [],
    }),
    computed: {
        allItemValues () {
            return this.items.map((row) => [ row[this.itemValue] ]).reduce((a, b) => a.concat(b))
        },
        allSelectIcon () {
            if (this.selectAll) return 'mdi-close-box'
            if (this.selectSome) return 'mdi-minus-box'
            return 'mdi-checkbox-blank-outline'
        },
        selectAll () {
            return this.selected.length === this.items.length
        },
        selectSome () {
            return 0 < this.selected.length && this.selected.length < this.items.length
        },
    },
    methods: {
        remove (value) {
            const index = this.selected.indexOf(value)
            if (index >= 0) this.selected.splice(index, 1)
        },
        toggleAll () {
            this.$nextTick(() => {
                if (this.selectAll) {
                    this.selected = []
                } else {
                    this.selected = this.allItemValues.slice()
                }
            })
        },
    },
    updated () {
        this.$emit('input', this.selected)
    },
    mounted () {
        this.selected = this.value
    },
}
</script>
