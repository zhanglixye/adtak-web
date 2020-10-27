<template>
<v-app>
    <v-content id="list">
        <file-Preview :inputs="inputs"></file-Preview>
    </v-content>
</v-app>
</template>

<script>
import FilePreview from '../../../Organisms/Admin/Approvals/FilePreview'

export default {
    components: {
        FilePreview
    },
    props: {
        inputs: { type: Object, required: true },
    }
}
</script>
