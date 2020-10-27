<template>
    <v-menu
            ref="menu"
            v-model="menu"
            :close-on-content-click="false"
            :return-value.sync="date"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
            style="width: 100%"
            :disabled="disabled"
    >
        <v-text-field
                v-model="date"
                :label="$attrs.label"
                prepend-icon="event"
                readonly
                slot="activator"
        ></v-text-field>

        <v-date-picker
                v-model="date"
                type="month"
                no-title
                scrollable
                locale="ja"
                @change="changeByPicker"
        >
        </v-date-picker>
    </v-menu>
</template>

<script>
export default {
    name: 'VDatePickerExtend',
    props:['value','disabled'],
    data(){
        return {
            date:this.value,
            menu: false,
        }
    },
    methods:{
        changeByPicker(){
            this.$emit('input',this.date);
            this.menu = false;
            this.$refs.menu.save(this.date)
        },
    }
}
</script>

<style scoped>

</style>