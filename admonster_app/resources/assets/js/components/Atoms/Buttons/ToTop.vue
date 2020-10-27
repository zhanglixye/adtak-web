<template>
    <v-fab-transition>
        <v-btn
            v-show="show"
            dark
            fab
            small
            right
            bottom
            floating
            fixed
            transition
            color="pink accent-1"
            id="btn-to-top"
            @click="toTop()"
          >
            <v-icon>keyboard_arrow_up</v-icon>
        </v-btn>
    </v-fab-transition>
</template>

<script>

export default {
    name: 'to-top',
    props: {
    },
    data() {
        return {
            show: false,
            scrollTimer: 0,
            scrollY: 0,
        }
    },
    created: function(){
        // scrollイベント登録
        window.addEventListener('scroll', this.scrollEvent)
    },
    methods: {
        //トップに戻る
        toTop: function() {
            let scrolled = window.pageYOffset
            window.scrollTo(0, Math.floor(scrolled * 0.8))
            if (scrolled > 0) {
                window.setTimeout(this.toTop, 10)
            }
        },
        // scrollイベントで現在のスクロール値を取得
        scrollEvent: function() {
            if (this.scrollTimer) return

            this.scrollTimer = setTimeout(() => {
                this.scrollY = window.scrollY
                if (this.scrollY > 300) {
                    this.show = true 
                } else {
                    this.show = false
                }
                clearTimeout(this.scrollTimer)
                this.scrollTimer = 0
            }, 100)
        }
    }
};
</script>

<style scoped>
#btn-to-top {
    bottom: 20px;
}
</style>
