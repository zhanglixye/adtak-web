<template>
    <div>
        <v-dialog v-model="relatedMailDetailDialog" persistent width="800">
            <v-card id="relatedMailDetailDialog">
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn small icon @click="close()"><v-icon>close</v-icon></v-btn>
                </v-card-actions>
                <v-card-text>
                    <mail :request-mail="data"></mail>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import mail from '../../../../../Molecules/Requests/RequestMail'

export default {
    props: {
        data: { type: Object, required: true },
        relatedMailDetailDialog: { type: Boolean, required: true },
    },
    data: () => ({
        dialog: false,
        loading: false,
    }),
    components:{
        mail
    },
    methods: {
        close () {
            this.$emit('update:relatedMailDetailDialog', false)
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
