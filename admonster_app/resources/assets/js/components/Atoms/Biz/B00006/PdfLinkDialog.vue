<template>
    <v-dialog v-model="dialog" width="1000">
<!--        <img :src="path"/>-->
        <iframe :src="path" frameborder="0"></iframe>
    </v-dialog>
</template>

<script>
export default {
    name: 'PdfLinkDialog',
    props:{
        axiosFormData:{type:FormData,require:true},
        loadingDisplay: { type: Function, require: true },
    },
    data () {
        return {
            dialog: false,
            path:'',
        }
    },
}
</script>

<style scoped>
    iframe{
        width: 100%;
        height: 800px;
    }
</style>