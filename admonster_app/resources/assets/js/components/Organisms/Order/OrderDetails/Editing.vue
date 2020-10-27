<template>
    <div class="elevation-1">
        <v-card
            color="light-blue"
            :height="height"
            dark
        >
            <v-card-actions>
                <v-tooltip top>
                    <v-btn icon slot="activator" @click="grow">
                        <v-icon>{{ arrowReverse ? 'mdi-chevron-double-right' : 'mdi-chevron-double-left' }}</v-icon>
                    </v-btn>
                    <span>{{ $t('order.order_details.show.information_component_management.header.grow') }}</span>
                </v-tooltip>
            </v-card-actions>
            <v-card-title>
                <span
                    class="headline vertical"
                    style="margin-left: 3px;"
                >
                    {{ $t('order.order_details.show.information_component_management.editing.title') }}
                </span>
            </v-card-title>
        </v-card>
    </div>
</template>

<script>
export default {
    props: {
        height: { type: String, required: false, default: '' },
        arrowReverse: { type: Boolean, required: false, default: false },
    },
    methods: {
        grow: function() {
            this.$emit('grow')
        },
    }
}
</script>

<style scoped>
.vertical {
    writing-mode: vertical-rl;
}
</style>
