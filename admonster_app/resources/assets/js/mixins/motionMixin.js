export default {
    created () {
        console.log('loaded motionMixin')
    },
    methods: {
        scrollToTop: function() {
            this.$vuetify.goTo('#app')
        }
    }
}
