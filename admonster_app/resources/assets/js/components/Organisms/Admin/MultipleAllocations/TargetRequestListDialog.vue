<template>
<v-dialog v-model="dialog" persistent fullscreen hide-overlay transition="dialog-bottom-transition">
    <v-card>
        <v-toolbar dark>
            <v-btn icon dark @click="dialog = false">
                <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>対象案件</v-toolbar-title>
          </v-toolbar>
        <v-container>
            <v-layout column wrap>
                <request-work :requests="requests" :candidates="candidates"></request-work>
            </v-layout>
        </v-container>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="dialog = false">閉じる</v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>
</template>

<script>
import RequestWork from '../../../Organisms/Common/RequestWork'

export default {
    props: {
        requests: { type: Array },
        candidates: { type: Array }
    },
    components: {
        RequestWork
    },
    data: () => ({
        dialog: true
    })
}
</script>
