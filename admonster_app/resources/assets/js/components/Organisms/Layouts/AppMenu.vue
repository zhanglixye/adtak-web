<template>
    <v-navigation-drawer
        app
        clipped
        v-model="drawerOpen"
        fixed
        dark
        width="256"
        class="side-menu"
        dense
    >
        <v-list>
            <v-list-tile class="menu-header">
                <v-list-tile-title></v-list-tile-title>
                <v-list-tile-action class="justify-end">
                    <v-btn
                        icon
                        @click.stop="handleSideMenuToggle"
                    >
                        <v-icon>chevron_left</v-icon>
                    </v-btn>
                </v-list-tile-action>
            </v-list-tile>

            <!-- ADMIN MENU -->
            <template v-if="isAdmin">
                <template v-for="(menu, index) in adminMenuList">
                    <template>
                        <template v-if="Object.keys(menu.topItems).length > 0">
                            <v-list-tile nuxt @click="link(menu.topItems.uri)" :disabled="menu.topItems.disabled" :key="'topItem_' + index">
                                <v-list-tile-action>
                                    <v-icon>{{ menu.topItems.icon }}</v-icon>
                                </v-list-tile-action>
                                <v-list-tile-title>{{ menu.topItems.title }}</v-list-tile-title>
                                <v-list-tile-action class="justify-center">
                                    <v-btn
                                        v-if="menu.topItems.badge && incompleteCount(menu.topItems.badge.name)"
                                        class="count-notice-btn"
                                        :ripple="false"
                                        small round depressed flat
                                        @click.stop="link(menu.topItems.badge.uri)"
                                    >
                                        <span :class="['pa-1', menu.topItems.badge.color]">{{ incompleteCount(menu.topItems.badge.name) }}</span>
                                    </v-btn>
                                </v-list-tile-action>
                            </v-list-tile>
                        </template>
                        <template v-if="menu.group.length > 0">
                            <ul class="mb-0 sub-menu-group" :key="'group_' + index">
                                <li v-for="item in menu.group" :key="item.title">
                                    <v-list-tile @click="link(item.uri)" :disabled="item.disabled">
                                        <v-list-tile-action></v-list-tile-action>
                                        <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                                        <v-list-tile-action class="justify-center">
                                            <v-btn
                                                v-if="item.badge && incompleteCount(item.badge.name)"
                                                class="count-notice-btn"
                                                :ripple="false"
                                                small round depressed flat
                                                @click.stop="link(item.badge.uri)"
                                            >
                                                <span :class="['pa-1', item.badge.color]">{{ incompleteCount(item.badge.name) }}</span>
                                            </v-btn>
                                        </v-list-tile-action>
                                    </v-list-tile>
                                </li>
                                <ul v-if="menu.subGroup.length > 0" class="mb-1">
                                    <li v-for="item in menu.subGroup" :key="item.title">
                                        <v-list-tile @click="link(item.uri)">
                                            <v-list-tile-action>
                                                <v-icon></v-icon>
                                            </v-list-tile-action>
                                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                                        </v-list-tile>
                                    </li>
                                </ul>
                            </ul>
                        </template>
                        <template v-else-if="menu.group.length < 1">
                            <ul class="mb-0 sub-menu-group" :key="'group_' + index">
                                <ul v-if="menu.subGroup.length > 0" class="mb-1">
                                    <li v-for="item in menu.subGroup" :key="item.title">
                                        <v-list-tile @click="link(item.uri)">
                                            <v-list-tile-action>
                                                <v-icon></v-icon>
                                            </v-list-tile-action>
                                            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                                        </v-list-tile>
                                    </li>
                                </ul>
                            </ul>
                        </template>
                        <v-divider v-if="menu.topItems.divider" class="ma-0" :key="menu.topItems.title"></v-divider>
                        <v-divider v-else-if="menu.subGroupDivider" class="ma-0" :key="menu.topItems.title"></v-divider>
                    </template>
                </template>
            </template>
            <!-- / ADMIN MENU -->

            <!-- WORKER MENU -->
            <template v-else v-for="(menu, index) in workerMenuList">
                <v-list-tile @click="link(menu.topItems.uri)" :key="menu.topItems.uri">
                    <v-list-tile-action>
                        <v-icon>list</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-title>{{ menu.topItems.title }}</v-list-tile-title>
                </v-list-tile>
                <template>
                    <ul class="mb-0" :key="index">
                        <li v-for="item in menu.group" :key="item.title">
                            <v-list-tile @click="link(item.uri)">
                                <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                            </v-list-tile>
                        </li>
                    </ul>
                </template>
                <v-divider v-if="menu.topItems.divider" class="ma-0" :key="menu.topItems.title"></v-divider>
            </template>
            <!-- / WORKER MENU -->

        </v-list>
    </v-navigation-drawer>
</template>

<script>
export default {
    props: {
        drawer: Boolean,
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        drawerOpen: null,
        items: [],
        incompleteCounts: {},
        isAdmin: false,
    }),
    computed: {
        adminMenuList () {
            return [
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('order.orders.list.title'), uri: '/order/orders', divider: true },
                    group: [],
                    subGroup: []
                },
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('menu.list.businesses'), uri: '/management/businesses', divider: true },
                    group: [],
                    subGroup: []
                },
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('menu.list.requests'), uri: '/management/requests', divider: false, badge: {name: 'request', uri: this.withGetParamUri('/management/requests', {'status': [_const.REQUEST_STATUS.DOING]}), color: 'red darken-3'} },
                    group: [
                        { title: Vue.i18n.translate('menu.list.allocations'), uri: '/allocations', badge: {name: 'allocation', uri: this.withGetParamUri('/allocations', {'status[]': [_const.ALLOCATION_STATUS.NONE]}), color: 'red darken-3'} },
                        { title: Vue.i18n.translate('menu.list.tasks'), uri: '/tasks', badge: {name: 'task', uri: this.withGetParamUri('/tasks', {'status[]': [_const.TASK_STATUS.NONE, _const.TASK_STATUS.ON]}), color: 'red darken-3'} },
                        { title: Vue.i18n.translate('menu.list.approvals'), uri: '/approvals', badge: {name: 'approval', uri: this.withGetParamUri('/approvals', {'status[]': [_const.APPROVAL_STATUS.NONE, _const.APPROVAL_STATUS.ON]}), color: 'red darken-3'} },
                        { title: Vue.i18n.translate('menu.list.deliveries'), uri: '/deliveries', badge: {name: 'delivery', uri: this.withGetParamUri('/deliveries', {'status[]': [_const.DELIVERY_STATUS.NONE, _const.DELIVERY_STATUS.SCHEDULED]}), color: 'red darken-3'} },
                    ],
                    subGroup: []
                },
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('menu.list.works'), uri: '/management/works', divider: true, badge: {name: 'task_contact', uri: this.withGetParamUri('/management/works', {'task_contact': [true]}), color: 'yellow accent-3 black--text'} },
                    group: [],
                    subGroup: []
                },
                {
                    topItems: { icon: 'insert_drive_file', title: Vue.i18n.translate('menu.function.import_file'), uri: '/imported_files', divider: true },
                    group: [],
                    subGroup: []
                },
                {
                    topItems: { icon: 'mdi-file-chart', title: Vue.i18n.translate('menu.function.reports'), uri: '/management/reports', divider: false },
                    group: [],
                    subGroup: []
                },
            ]
        },
        workerMenuList () {
            return [
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('order.orders.list.title'), uri: '/order/orders', divider: true },
                    group: [],
                    subGroup: []
                },
                {
                    topItems: { icon: 'list', title: Vue.i18n.translate('menu.list.tasks'), uri: '/tasks', divider: false },
                    group: []
                }
            ]
        },
        incompleteCount () {
            return (key) => {
                const maxCount = 99
                const count = this.incompleteCounts[key] ? this.incompleteCounts[key] : 0
                return count > maxCount ? maxCount + '+' : count
            }
        },
    },
    methods: {
        link (uri) {
            window.location.href = uri
        },
        handleSideMenuToggle () {
            eventHub.$emit('app-menu-toggled')
        },
        withGetParamUri (url, params) {
            let uri = url;
            Object.keys(params).forEach(key => {
                params[key].forEach((val) => {
                    if (uri !== url)  {
                        uri = uri + '&' + encodeURIComponent(key) + '=' + encodeURIComponent(val)
                    } else {
                        uri = uri + '?' + encodeURIComponent(key) + '=' + encodeURIComponent(val)
                    }
                })
            })
            return uri
        },
        async getContents () {
            await axios.get('/api/utilities/getSideMenuContents')
                .then((res) =>{
                    this.incompleteCounts = res.data.incomplete_counts
                    this.isAdmin = res.data.is_admin
                }).catch((error) => {
                    console.log(error)
                })
        },
    },
    created () {
        eventHub.$on('app-menu-toggled', () => {
            this.drawerOpen = (!this.drawerOpen);
        });
        this.drawerOpen = this.drawer && !this.referenceMode
        // サイドメニュー用コンテンツを取得する
        this.getContents()
    },
}
</script>
<style scope>
.count-notice-btn {
    min-width: 28px;
}
.count-notice-btn span {
    min-width: 23px;
    height: 23px;
    align-items: center;
    border-radius: 12px;
    display: inline-flex;
    font-size: 12px;
    justify-content: center;
    transition: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
</style>
