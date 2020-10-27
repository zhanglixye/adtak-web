<template>
    <v-toolbar
        v-if="show"
        :clipped-left="$vuetify.breakpoint.lgAndUp"
        color="primary"
        dark
        app
        fixed
        flat
        dense
        id="header"
        class="teal"
    >
        <v-toolbar-title>
            <v-toolbar-side-icon @click.stop="handleSideMenuToggle" v-if="!isGuest"></v-toolbar-side-icon>
            <span class="hidden-sm-and-down">{{ appName }}</span>
            <span class="header-vertical-line mr-2 ml-2">|</span>
            <span class="hidden-sm-and-down">
                {{ title }}
                <span class="subheading" v-show="subtitle">{{ subtitle }}</span>
            </span>
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
            <!-- <v-menu offset-y>
                <v-btn slot="activator" flat>
                    <span>{{ locale }}</span>
                </v-btn>
                <v-list dense class="pa-0">
                    <v-list-tile
                        v-for="(item, index) in languages"
                        :key="index"
                        @click="switchLanguage(item)"
                    >
                        <v-list-tile-content>
                            <v-list-tile-title>{{ item.label }}</v-list-tile-title>
                        </v-list-tile-content>
                    </v-list-tile>
                </v-list>
            </v-menu> -->
            <v-menu offset-y>
                <v-btn slot="activator" flat>
                    <v-avatar size="32px" :tile="false">
                        <img :src="userImage">
                    </v-avatar>
                    <span class="pa-2">{{ userName }}</span>
                </v-btn>
                <v-list dense class="pa-0">
                    <template v-for="(item, index) in items">
                        <v-list-tile
                            :key="index"
                            :href="item.href"
                            @click="action(item.action)"
                        >
                            <v-list-tile-action>
                                <v-icon>{{ item.icon }}</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </template>
                </v-list>
            </v-menu>
        </v-toolbar-items>
    </v-toolbar>
</template>

<script>
export default {
    props: {
        title: { type: String },
        subtitle: { type: String },
        back_uri: { type: String },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        items: [
            { icon: 'account_circle', title: '個人設定', href: '/management/user', },
            { icon: 'exit_to_app', title: 'logout', href: '/logout', action: 'logout' },
        ],
        languages: [
            { label: '日本語', key: 'ja' },
            { label: 'ENGLISH', key: 'en' }
        ],
        locale: '日本語'
    }),
    created () {
        this.init()
    },
    computed: {
        appName () {
            return _const.APP_NAME
        },
        userId () {
            return document.getElementById('login-user-id').value
        },
        userName () {
            return this.isGuest ? 'Guest' : document.getElementById('login-user-name').value
        },
        userImage () {
            return document.getElementById('login-user-image').value ? document.getElementById('login-user-image').value : '/images/dummy_icon.png'
        },
        userTimezone () {
            return document.getElementById('login-user-timezone').value
        },
        isGuest () {
            return document.getElementById('login-guest-user').value === 'true'
        },
        show () {
            return this.referenceMode ? false : true
        },
    },
    methods: {
        init () {
            // 作業画面は設定しない
            if (location.pathname.startsWith('/biz/')) return
            // ユーザ指定タイムゾーンを設定
            moment.tz.setDefault(this.userTimezone)
        },
        logout () {
            event.preventDefault()
            document.getElementById('logout-form').submit()
        },
        handleSideMenuToggle () {
            eventHub.$emit('app-menu-toggled')
        },
        switchLanguage (param) {
            Vue.i18n.set(param.key)
            this.locale = param.label
        },
        action (type) {
            switch (type) {
            case 'logout':
                this.logout()
                break;

            default:
                break;
            }
        }
    }
}
</script>
