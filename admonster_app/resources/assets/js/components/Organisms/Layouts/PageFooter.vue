<template>
    <div id="page-footer">
        <div class="btn-center-block" v-if="showBackButton()">
            <v-tooltip top>
                <template slot="activator">
                    <v-btn depressed icon color="primary" @click="backHistory()">
                        <v-icon color="white">arrow_back</v-icon>
                    </v-btn>
                </template>
                <span>{{ $t('common.button.back') }}</span>
            </v-tooltip>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        backButton: { type: Boolean, required: false, default: false },
        prependFunctionToBackHistory: { type: Function, required: false, default: () => {} },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    methods: {
        showBackButton () {
            return this.backButton && this.existsHistory() && !this.referenceMode
        },
        existsHistory () {
            return window.history.length > 1
        },
        async backHistory () {
            await this.prependFunctionToBackHistory()
            window.history.back()
        }
    }
}
</script>
