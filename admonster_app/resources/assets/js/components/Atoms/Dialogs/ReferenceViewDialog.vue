<template>
    <v-dialog
        v-model="dialog"
        :content-class="dialogClass"
        :width="width"
        max-width="1024"
        @close="close"
    >
        <v-icon
            class="dialog-close"
            large
            color="white"
            @click="close"
        >clear</v-icon>
        <div class="reference-content">
            <iframe
                :src="uri"
                frameborder="0"
                class="dialog-iframe"
            ></iframe>
        </div>
    </v-dialog>
</template>

<script>
export default {
    props: {
        width: { type: String, required: false, default: 'calc(100% - 100px)' },
        contentClass: { type: String, required: false, default: '' },
    },
    data: () => ({
        dialog: false,
        uri: '',
    }),
    computed: {
        dialogClass () {
            return 'reference-dialog' + (this.contentClass ? ' ' + this.contentClass : '')
        },
    },
    methods: {
        show (uris, type) {
            if (type) {
                this.windowOpen(uris, type)
                return
            }
            this.uri = this.setUri(uris.shift())
            this.dialog = true
        },
        close () {
            this.uri = ''
            this.dialog = false
        },
        setUri (uri) {
            return uri + (uri.split('?').length > 1 ? '&' : '?') + 'reference_mode=true'
        },
        async windowOpen (uris, type) {
            const move_x = 340
            const move_y = 200
            let left = 0
            let top = 0
            uris.forEach(uri => {
                const feature = type == 'window' ? 'width=710, height=500, resizable=1, top=' + top + ', left=' + left : ''
                window.open(this.setUri(uri), '_blank', feature)
                // 位置調整
                left += move_x
                if (left > move_x * 2) {
                    top += move_y
                    left = 0
                }
                top = top > move_y * 2 ? 0 : top
            })
        },
    },
    watch: {
        dialog (val) {
            val || this.close();
        },
    },
}
</script>
<style scoped>
.reference-content {
    width: 100%;
    height: 100%;
}
.dialog-close {
    position: absolute;
    right: 0;
    top: 0;
    background-color: transparent;
}
.dialog-iframe {
    width: 100%;
    height: 100%;
}
</style>
<style>
.reference-dialog:not(.fill-height) {
    height: 70%;
}
</style>
