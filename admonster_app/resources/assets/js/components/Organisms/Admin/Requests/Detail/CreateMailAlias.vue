<template>
    <div id="create-mail-alias">
        <!-- タイトル部分 -->
        <a class="subheading font-weight-bold text-underline" @click="createFormDialog = true"><span>{{ $t('requests.detail.related_mails.link_text_import') }}</span></a>
        <!-- /タイトル部分 -->
        <create-mail-alias-dialog
            :create-form-dialog.sync="createFormDialog"
            :request-id="requestId"
            :request-work-id="requestWorkId"
        >
        </create-mail-alias-dialog>
    </div>
</template>

<script>
import CreateMailAliasDialog from './CreateMailAliasDialog'

export default {
    name: 'CreateMailAlias',
    components: {
        CreateMailAliasDialog
    },
    props: {
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number },
    },
    data:() =>({
        createFormDialog: false
    }),
}
</script>
