<template>
<v-dialog v-model="dialog" persistent max-width="600px">
    <v-btn
        slot="activator"
        flat
        small
        color="primary"
        >
        <v-icon>mail</v-icon>
        {{ $t('allocations.allocation_list.remind_dialog.icon_name') }}
    </v-btn>
    <v-card>
        <v-card-title>
            <span class="headline"><v-icon>mail</v-icon>リマインド設定</span>
        </v-card-title>
        <v-container>
            <v-layout column wrap>
                <v-list dense>
                    <v-list-tile>
                        <v-list-tile-action>
                            <v-checkbox color="primary"></v-checkbox>
                        </v-list-tile-action>
                        <v-list-tile-action>
                            <v-autocomplete
                              :items="['Aさん', 'Bさん', 'Cさん']"
                              multiple
                              style="width: 200px;"
                            ></v-autocomplete>
                        </v-list-tile-action>
                        <v-list-tile-action-text>
                            の作業終了時にメールを受け取る
                        </v-list-tile-action-text>
                    </v-list-tile>
                    <v-list-tile>
                        <v-list-tile-action>
                            <v-checkbox color="primary"></v-checkbox>
                        </v-list-tile-action>
                        <v-list-tile-action>
                            <v-select
                              :items="['30', '60', '90']"
                              required
                              style="width: 50px;"
                            ></v-select>
                        </v-list-tile-action>
                        <v-list-tile-action-text>
                            分後にメールを受け取る
                        </v-list-tile-action-text>
                    </v-list-tile>
                    <v-list-tile>
                        <v-list-tile-action>
                            <v-checkbox color="primary"></v-checkbox>
                        </v-list-tile-action>
                        <v-list-tile-action-text>
                            作業終了見込み時間にメールを受け取る
                        </v-list-tile-action-text>
                    </v-list-tile>
                </v-list>
            </v-layout>
        </v-container>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="dialog = false">閉じる</v-btn>
            <v-btn color="primary" flat @click="dialog = false">設定</v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>
</template>

<script>
export default {
    data: () => ({
        dialog: false
    })
}
</script>
