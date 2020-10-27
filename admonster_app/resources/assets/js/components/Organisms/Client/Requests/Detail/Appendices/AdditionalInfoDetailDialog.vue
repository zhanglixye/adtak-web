<template>
    <div>
        <v-dialog v-model="additionalInfoDialog" persistent width="600">
            <v-card id="additionalInfoDialog">
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn small icon @click="close()"><v-icon>close</v-icon></v-btn>
                </v-card-actions>
                <v-card-text>{{ data.content }}</v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    props: {
        data: { type: Object, required: true },
        additionalInfoDialog: { type: Boolean, required: true },
    },
    data: () => ({
        dialog: false,
        loading: false,
    }),
    methods: {
        close () {
            this.$emit('update:additionalInfoDialog', false)
        },
    }
}
</script>
<style scoped>
/*2行の3点リーダー*/
.appendix-text-wrap {
    overflow: hidden;
    width: 100%;
}
.appendix-text-wrap .appendix-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
