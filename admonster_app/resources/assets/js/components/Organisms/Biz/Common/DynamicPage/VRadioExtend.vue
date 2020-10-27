<template>
    <div>
        <p>{{ label}}</p>
        <v-radio-group row v-model="valueRadioExtend" :disabled="disabled">
            <slot>
                <v-radio
                        class="mr-10 fw"
                        v-for="(i,o) in items"
                        :key="o"
                        :label="i.label"
                        :value="i.value"
                >
                </v-radio>
            </slot>
        </v-radio-group>
    </div>
</template>

<script>
export default {
    name: 'VRadioExtend',
    props:['items','value','label','disabled'],
    data(){
        return {
            valueRadioExtend:this.value,
        }
    },
    watch:{
        valueRadioExtend(value){
            this.$emit('input',value);
        }
    }
}
</script>

<style scoped>

</style>