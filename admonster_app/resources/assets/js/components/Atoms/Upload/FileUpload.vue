<template>
    <div :class="[{'-drag': isDrag == 'new', '-undrag': isDrag != 'new'}]"
        @dragover.prevent="checkDrag($event, 'new', true)"
        @dragleave.prevent="checkDrag($event, 'new', false)"
        @drop.prevent="onDrop"
        v-if="!disabled"
    >
        <i aria-hidden="true" class="fa fa-plus"></i>
        <div class="drop">
            <div class="pa-3">
                <p class="drag-drop-info text-center">{{$t('file_upload.come_here_drop')}}</p>
                <p class="text-center">{{$t('file_upload.or')}}</p>
                <div class="text-xs-center">
                    <v-btn
                        :for="emit_message + 'upload_check_files'"
                        class="primary btn file-upload white--text"
                        @click="$refs.inputUpload.click()"
                    >
                        {{$t('file_upload.pick_file')}}
                    </v-btn>
                    <input type="file" class="drop__input" style="display:none;"
                        ref="inputUpload"
                        :id="emit_message + 'upload_check_files'"
                        name="upload_check_files"
                        app
                        @change="onDrop"
                        multiple
                    >
                </div>
            </div>
        </div>
    </div>
    <div :class="[{'-drag': isDrag == 'new', '-undrag': isDrag != 'new'}]"
        style="cursor: not-allowed;"
        v-else
    >
        <i aria-hidden="true" class="fa fa-plus"></i>
        <div class="drop">
            <div class="pa-3">
                <p class="drag-drop-info text-center">{{$t('file_upload.come_here_drop')}}</p>
                <p class="text-center">{{$t('file_upload.or')}}</p>
                <div class="text-xs-center">
                    <v-btn
                        :for="emit_message + 'upload_check_files'"
                        class="color" :disabled="disabled"
                        @click="$refs.inputUpload.click()"
                    >
                        {{$t('file_upload.pick_file')}}
                    </v-btn>
                    <input type="file" class="drop__input" style="display:none;"
                        ref="inputUpload"
                        :id="emit_message + 'upload_check_files'"
                        name="upload_check_files"
                        app
                        @change="onDrop"
                        multiple
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import fileUploadMixin from './../../../mixins/fileUploadMixin'
export default {
    mixins: [fileUploadMixin],
};
</script>

<style scoped>

.-undrag{
    border: dashed;
    border-color: gray;
}

.-drag{
    border: dashed;
    border-color: red;
}

</style>
