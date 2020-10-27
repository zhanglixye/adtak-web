<template>
    <div class="mt-3">
        <a @click="showPdfLink">{{label}}</a>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </div>
</template>

<script>
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    name: 'AExtend',
    components: {FilePreviewDialog},
    props:['value','label','disabled'],
    data(){
        return {

        }
    },
    methods:{
        showPdfLink(){
            const file = {
                name: this.label,
                file_path: this.value
            }
            console.log(file);
            let type = '';
            this.$refs.filePreviewDialog.show([file], [], type);
        }
    }
}
</script>

<style scoped lang="scss">
    a{
        font-size: 12px;
        color: #1976d2;
        text-decoration: underline;
    }
</style>