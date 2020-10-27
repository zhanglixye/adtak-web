<template>
    <div>
        <div class="page-header-alert">
            <template v-for="alert in alerts">
                <v-alert
                    :key="alert.message"
                    transition="slide-y-transition"
                    v-model="alert.model"
                    outline
                    :dismissible="alert.dismissible"
                    :color="alert.color ? alert.color : ''"
                >
                    <div class="alert-body">
                        <span class="mr-2">
                            <v-icon>{{ alert.icon }}</v-icon>
                        </span>
                        <span class="alert-message">
                            {{ alert.message }}
                        </span>
                    </div>
                </v-alert>
            </template>
        </div>
        <div class="page-header-main">
            <div v-if="showBackButton()">
                <v-btn flat small :dark="dark" class="ma-0" @click="backHistory()">
                    <v-icon left>arrow_back</v-icon>{{ $t('common.button.back') }}
                </v-btn>
            </div>
            <v-spacer></v-spacer>
            <slot name="rightHeader"></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        alerts: { type: Object, required: false, default: null },
        backButton: { type: Boolean, required: false, default: false },
        dark: { type: Boolean, required: false, default: false },
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

<style scope>
.page-header-main,
.page-header-alert .alert-body {
    display: flex;
    align-items: center;
    justify-content: center;
}
.page-header-alert .alert-body .v-icon {
    font-size: 26px;
}
.page-header-alert .alert-body .alert-message {
    font-size: 18px;
}
</style>
