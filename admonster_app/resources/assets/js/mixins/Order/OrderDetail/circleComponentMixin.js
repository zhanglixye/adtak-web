export default {
    props: {
        mode: { type: Number, required: true },
        hideLeftGrowButton: { type: Boolean, required: false, default: false },
        hideRightGrowButton: { type: Boolean, required: false, default: false },
        fullWidth: { type: Boolean, required: false, default: false },
        height: { type: [String, Number], required: false, default: undefined },
    },
    computed: {
        headerColor() {
            const defaultColor = 'primary'
            return this.isEditMode ? 'light-blue' : defaultColor
        },
        showLeftGrowButton() {
            return (!this.fullWidth && !this.hideLeftGrowButton)
        },
        showRightGrowButton() {
            return (!this.fullWidth && !this.hideRightGrowButton)
        },
        showShrinkButtons() {
            return this.fullWidth
        },
        headerHeight() {
            return 40
        },
        contentHeight() {
            const contentHeight = this.height ? `${parseInt(this.height, 10)}px` : null
            if (contentHeight === null) return ''
            return `calc(${contentHeight} - ${this.headerHeight}px)`
        },
        isReadonlyMode() {
            return [_const.DISPLAY_MODE.READ_ONLY].includes(this.mode)
        },
        isEditableMode() {
            return [_const.DISPLAY_MODE.EDIT_OFF].includes(this.mode)
        },
        isEditMode() {
            return [_const.DISPLAY_MODE.EDIT_ON].includes(this.mode)
        },
    },
    methods: {
        previous: function () {
            this.$emit('previous')
        },
        next: function () {
            this.$emit('next')
        },
        grow: function () {
            this.$emit('grow')
        },
        shrinkRight: function () {
            this.$emit('shrink-right')
        },
        shrinkLeft: function () {
            this.$emit('shrink-left')
        },
        toEditMode: async function () {
            this.$emit('to-edit-mode')
        },
        toReadMode: function () {
            this.$emit('to-read-mode')
        },
        openCreateRequest: function () {
            this.$emit('open-create-request')
        },
        closeCreateRequest: function () {
            this.$emit('close-create-request')
        },
        openCreateMail: function () {
            this.$emit('open-create-mail')
        },
        closeCreateMail: function () {
            this.$emit('close-create-mail')
        },
    }
}
