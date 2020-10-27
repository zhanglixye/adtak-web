<template>
    <v-tooltip top>
        <v-img
            slot="activator"
            aspect-ratio="1"
            :max-height="maxHeight"
            :max-width="maxWidth"
            :src="url"
        ></v-img>
        <span>{{ fileInfo['name'] }}</span>
    </v-tooltip>
</template>

<script>
export default {
    props: {
        fileInfo: { type: Object, require: true },
        maxHeight: { type: Number, require: false, default: 100 },
        maxWidth: { type: Number, require: false, default: 100 },
    },
    data: () => ({
        url: '/images/file-outline.svg',  // default
    }),
    created () {
        this.init()
    },
    methods: {
        async init () {
            try {
                let res = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail', {
                    params: { 'file_path': this.fileInfo['file_path'] }
                })
                if (res.data.status === 200) {
                    this.url = res.data['url']
                } else {
                    console.warn(res.data)
                }
            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>
