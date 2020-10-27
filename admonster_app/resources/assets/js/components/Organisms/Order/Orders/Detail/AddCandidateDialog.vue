<template>
    <v-dialog
        v-model="dialog"
        persistent
        width="1000"
    >
        <v-card>
            <v-card-title
                class="headline grey lighten-2"
                primary-title
            >
                <span v-if="isAdmin">{{ $t('order.orders.dialog.order_admin.title') }}</span>
                <span v-else>{{ $t('order.orders.dialog.order_sharer.title') }}</span>
                <v-spacer></v-spacer>
                <v-list-tile-action>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        :label="$t('order.orders.dialog.order_admin.search')"
                        single-line
                        hide-details
                        class="pa-0"
                    ></v-text-field>
                </v-list-tile-action>
            </v-card-title>

            <v-card-text>
                <v-data-table
                    v-model="users"
                    :headers="headers"
                    :items="candidateUsers"
                    item-key="id"
                    :search="search"
                    hide-actions
                    must-sort
                    :pagination.sync="pagination"
                    style="height: 66vh;overflow-y: auto;"
                >
                    <template slot="headerCell" slot-scope="props">
                        <v-tooltip top v-if="props.header.detail">
                            <span slot="activator">{{ props.header.text }}</span>
                            <span>{{ props.header.detail }}</span>
                        </v-tooltip>
                        <span v-else>{{ props.header.text }}</span>
                    </template>
                    <template slot="items" slot-scope="props">
                        <tr :active="props.selected">
                            <td class="text-xs-center pa-0">
                                <v-checkbox
                                    :input-value="props.selected"
                                    primary
                                    hide-details
                                    color="primary"
                                    @click="props.selected = !props.selected"
                                ></v-checkbox>
                            </td>
                            <td class="text-xs-center pa-0">{{ props.item.id }}</td>
                            <td class="text-xs-right">{{ props.item.name }}</td>
                            <td class="text-xs-right">{{ props.item.email }}</td>
                            <td class="text-xs-center">
                                <template>
                                    <v-avatar size="32px" :tile="false">
                                        <img :src="userImageSrc(props.item.user_image_path)">
                                    </v-avatar>
                                </template>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="grey"
                    dark
                    text
                    @click="dialog = false"
                >
                    {{ $t('common.button.cancel') }}
                </v-btn>
                <v-btn
                    color="primary"
                    text
                    @click="addOperator"
                >
                    {{ $t('common.button.add') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        candidateUsers: { type: Array, require: true },
    },
    data: () => ({
        dialog: false,
        selected: [],
        users: [],
        title: '',
        search: '',
        isAdmin: false,

        pagination: {
            sortBy: 'average',
            descending: false,
            rowsPerPage: -1, // ALL
        },
    }),
    methods: {
        show(isAdmin = true) {
            this.isAdmin = isAdmin
            this.users = []
            this.select = []
            this.dialog = true
        },
        close() {
            this.dialog = false
        },
        addOperator() {
            if (this.isAdmin) {
                this.$emit('add-administrators', this.users)
            } else {
                this.$emit('add-sharers', this.users)
            }
        },
    },
    computed: {
        headers() {
            return [
                { text: '', value: 'master.users.headers.id', align: 'center', sortable: false },
                { text: eventHub.$t('master.users.headers.id'), value: 'id', align: 'center' },
                { text: eventHub.$t('master.users.headers.user_name'), value: 'user_name', align: 'center' },
                { text: eventHub.$t('master.users.headers.email'), value: 'email', align: 'center' },
                { text: eventHub.$t('master.users.headers.user_image_path'), value: 'user_image_path', align: 'center' }
            ]
        },
        userImageSrc() {
            return function(user_image_path) {
                if (user_image_path) {
                    return user_image_path
                } else {
                    return location.origin + '/images/dummy_icon.png'
                }
            }
        },
    },
    watch: {
        selected() {
            this.users = this.selected
        },
    },
    created() {
        this.users = this.selected
    },
}
</script>

<style scoped></style>
