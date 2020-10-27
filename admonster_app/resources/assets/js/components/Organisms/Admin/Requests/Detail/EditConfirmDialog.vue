<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('requests.confirm') }}</v-card-title>
            <v-card-text>
                {{ confirmText }} よろしいですか？
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat color="primary" @click="save()">{{ $t('common.button.ok') }}</v-btn>
                <v-btn flat color="primary" @click="close()">{{ $t('common.button.cancel') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        dialog: { type: Boolean, require: true },
        type: { type: String, require: true },
        selectedId: { type: Number, require: true },
        callback: { type: String, require: true }
    },
    computed: {
        confirmText () {
            let text = ''
            if (this.type == 'edit') {
                text = '更新します。'
            } else if (this.type == 'delete') {
                text = '削除します。'
            }
            return text
        }
    },
    methods: {
        save () {
            this.$emit('update:dialog', false)
            eventHub.$emit(this.callback, this.selectedId)
        },
        close () {
            this.$emit('update:dialog', false)
        }
    }
}
</script>
