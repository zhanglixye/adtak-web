<template>
    <div class="d-flex align-center">
        <span class="pr-2">
        <!-- eslint-disable vue/valid-v-model -->
        <v-text-field
            slot="activator"
            placeholder="yyyy/mm/dd"
            :disabled="isActive"
            hide-details
            reverse
            v-model="isActive ? dateValue :textValue"
            @blur="inputChange()"
        ></v-text-field>
        <!-- eslint-able vue/valid-v-model -->
        </span>
            <span class="ml-1" style="display:contents;">
            <v-menu
                ref="item"
                :close-on-content-click="false"
                v-model="item"
                :nudge-right="40"
                :return-value.sync="textValue"
                lazy
                transition="scale-transition"
                offset-y
                full-width
                :disabled="isActive"
            >
            <v-icon
                    slot="activator"
            >event</v-icon>
            <v-date-picker v-model="date" no-title scrollable>
                <v-spacer></v-spacer>
                <v-btn flat color="primary" @click="item = false">Cancel</v-btn>
                <v-btn flat color="primary" @click="$refs.item.save(formatDate(date))">OK</v-btn>
            </v-date-picker>
            </v-menu>
        </span>
    </div>
</template>

<script>
import formatMixin from '../../../../mixins/Biz/formatMixin'
import motionMixin from '../../../../mixins/motionMixin'

export default {
    name: 'DatePicker',
    mixins: [formatMixin, motionMixin],
    data: () => ({
        item: false,
        date: new Date().toISOString().substr(0, 10),
        textValue: '',
    }),
    props: {
        isActive: Boolean,
        dateValue: String,
    },
    methods: {
        inputChange: function () {
            const dateFormat = ['MM-DD-YYYY', 'YYYY-MM-DD']
            const minDay = moment('1000/01/01',dateFormat).valueOf()
            const MaxDay = moment('9999/12/31',dateFormat).valueOf()
            if (minDay <= moment(this.textValue,dateFormat).valueOf() && moment(this.textValue,dateFormat).valueOf() <= MaxDay) {
                if ( moment(this.textValue,dateFormat).isValid()){
                    this.textValue = moment(this.textValue,dateFormat).format('YYYY/MM/DD')
                    this.date = moment(this.textValue,dateFormat).format('YYYY-MM-DD')
                } else {
                    this.textValue = ''
                }
            }
            else {
                this.textValue = ''
            }
        }
    },
    watch: {
        'textValue': function () {
            this.$emit('dateValue', this.textValue)
        }
    }
}
</script>
