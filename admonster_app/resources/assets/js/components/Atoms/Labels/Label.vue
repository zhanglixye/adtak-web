<template>
    <span>{{ langData[currentLangCode][labelId] }}</span>
</template>

<script>
export default {
    name: 'Label',
    props: {
        labelId: { type: Number, require: true },
        langData: { type: Object, require: true }
    },
    data: () => ({
        currentLangCode: Vue.i18n.locale()
    }),
    methods: {
    }
}
</script>
