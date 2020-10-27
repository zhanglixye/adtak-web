<template>
<v-footer v-if="show" inset class="white pa-3">
    <v-spacer></v-spacer>
    <span class="caption">Copyright &copy; ADPRO. inc. All Rights Reserved.</span>
</v-footer>
</template>

<script>
export default {
    props: {
        referenceMode: { type: Boolean, required: false, default: false },
    },
    computed: {
        show () {
            return this.referenceMode ? false : true
        },
    },
}
</script>
