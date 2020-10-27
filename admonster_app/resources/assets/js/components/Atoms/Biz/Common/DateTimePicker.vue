<template>
    <div :class="isHiddenIcon ? 'd-flex' : 'd-flex px-1'">
        <!-- <v-flex xs7> -->
            <v-menu
                v-model="dateMenu"
                :close-on-content-click="false"
                :nudge-right="40"
                lazy
                transition="scale-transition"
                offset-y
                full-width
                min-width="290px"
                :disabled="disabled"
            >
                <template>
                    <v-text-field
                        style="min-width: 121px"
                        v-model="textDateValue"
                        :label="dateTypeLabel"
                        placeholder="YYYY/MM/DD"
                        :prepend-icon="isHiddenIcon ? '' : 'event'"
                        :disabled="disabled"
                        slot="activator"
                        hide-details
                        @blur="dateInputChange()"
                    ></v-text-field>
                </template>
                <v-date-picker v-model="date" @input="dateMenu = false"></v-date-picker>
            </v-menu>
        <!-- </v-flex> -->
        <div v-if="isHiddenIcon" style="width: 24px;"></div>
        <!-- <v-flex xs5> -->
            <v-menu
                ref="timeMenu"
                v-model="timeMenu"
                :close-on-content-click="false"
                :nudge-right="40"
                lazy
                transition="scale-transition"
                offset-y
                full-width
                max-width="290px"
                min-width="290px"
                :disabled="disabled || (this.date ? false : true)"
            >
                <template >
                    <v-text-field
                        v-model="textTimeValue"
                        style="min-width: 77px"
                        :prepend-icon="isHiddenIcon ? '' : 'access_time'"
                        :disabled="disabled"
                        slot="activator"
                        placeholder="HH:mm"
                        hide-details
                        @blur="timeInputChange()"
                    ></v-text-field>
                </template>
                <v-time-picker
                    v-if="timeMenu"
                    v-model="time"
                    full-width
                    format="24hr"
                    @change="$refs.timeMenu.save(time)"
                ></v-time-picker>
            </v-menu>
        <!-- </v-flex> -->
    </div>
</template>

<script>
import formatMixin from '../../../../mixins/Biz/formatMixin'
import motionMixin from '../../../../mixins/motionMixin'
export default {
    mixins: [formatMixin, motionMixin],
    data: () =>({
        time: null,
        dateMenu: false,
        date: null,
        timeMenu: false,
        textDateValue: '',
        dateTime: '',
        textTimeValue: '',
    }),
    props: {
        dateTypeLabel: String,
        from : Boolean,
        format: {
            type: String,
            default: 'YYYY/MM/DD HH:mm'
        },
        value: {
            type: [String, Date, moment, Number],
            default: '',
            required: false
        },
        isHiddenIcon: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
    },
    created() {
        this.convertToDateAndTime()
    },
    watch:{
        date () {
            if (this.date != null){
                this.textDateValue = this.formatDate(this.date)
                if (this.from==true) {
                    if (this.time == null) {
                        this.time= '00:00'

                    } else {
                        this.textTimeValue = this.time
                    }
                    this.dateTime =this.textDateValue  +' '+ this.time
                }
                if (this.from==false) {
                    if (this.time == null) {
                        this.time= '23:59'
                    } else {
                        this.textTimeValue =this.time
                    }
                    this.dateTime =this.textDateValue  +' '+ this.time
                }
            }
            else {
                this.time = null
                this.dateTime = ''
                this.textTimeValue = ''

            }
        },
        time () {
            if (this.time !=null && this.date != null){
                this.textTimeValue = this.time
                this.dateTime =this.textDateValue +' '+ this.textTimeValue
            }
        },
        'dateTime': function () {
            this.$emit('dateTime', this.dateTime ? this.from ? moment(this.dateTime, 'YYYY/MM/DD HH:mm').utc(): moment(this.dateTime, 'YYYY/MM/DD HH:mm').utc().second(59) : '')
        },
        value () {
            this.convertToDateAndTime()
        }
    },
    methods: {
        convertToDateAndTime () {
            // Type checker
            const typeEquals = (type, obj) => {
                var clas = Object.prototype.toString.call(obj).slice(8, -1)
                return clas === type;
            }

            // Convert to outputFormat
            let defaultMoment = ''
            const outputFormat = 'YYYY/MM/DD HH:mm'
            if ( typeEquals('String',this.value)) {

                // valu format convert "this.format" to "outputFormat"
                if (moment(this.value, this.format, true).isValid()) defaultMoment = moment(this.value, this.format).format(outputFormat)

            } else if (typeEquals('Number',this.value) || typeEquals('Date',this.value) || this.value._isAMomentObject) {

                // Unix time epoch
                defaultMoment = moment.utc(this.value).local().format(outputFormat)
            } else {
                return
            }

            // Set date and time
            if (moment(defaultMoment, outputFormat, true).isValid()) {
                const [date, time] = defaultMoment.split(' ')
                this.textDateValue = date
                this.textTimeValue = time
                this.dateInputChange()
                this.timeInputChange()
            }
        },
        formatDate (date) {
            if (!date) return null
            const [year, month, day] = date.split('-')
            return `${year}/${month}/${day}`
        },
        dateInputChange: function () {
            const dateFormat = ['MM-DD-YYYY', 'YYYY-MM-DD']
            const minDay = moment('1000/01/01',dateFormat).valueOf()
            const MaxDay = moment('9999/12/31',dateFormat).valueOf()
            if (minDay <= moment(this.textDateValue,dateFormat).valueOf() && moment(this.textDateValue,dateFormat).valueOf() <= MaxDay) {
                if ( moment(this.textDateValue,dateFormat).isValid() ){
                    this.textDateValue = moment(this.textDateValue,dateFormat).format('YYYY/MM/DD')
                    this.date = moment(this.textDateValue,dateFormat).format('YYYY-MM-DD')
                    this.dateTime =this.textDateValue +' '+ this.time
                } else {
                    this.textDateValue = ''
                    this.date = null
                }
            }
            else {
                this.textDateValue = ''
                this.date = null
            }
        },
        timeInputChange: function () {
            const timeFormat = ['HH:mm']
            const minTime = moment('00:00',timeFormat).valueOf()
            const maxTime = moment('23:59',timeFormat).valueOf()
            if (minTime <= moment(this.textTimeValue,timeFormat).valueOf() && moment(this.textTimeValue,timeFormat).valueOf() <= maxTime) {
                if (moment(this.textTimeValue,timeFormat).isValid()) {
                    this.textTimeValue = moment(this.textTimeValue,timeFormat).format('HH:mm')
                    this.time =moment(this.textTimeValue,timeFormat).format('HH:mm')
                    this.dateTime = this.textDateValue + ' ' + this.textTimeValue
                } else {
                    this.textTimeValue = ''
                    this.time = null
                }
            }
            else {
                if (this.from == true){
                    this.textTimeValue = '00:00'
                    this.time =moment(this.textTimeValue,timeFormat).format('HH:mm')
                    this.dateTime = this.textDateValue + ' ' + this.textTimeValue
                } else {
                    this.textTimeValue = '23:59'
                    this.time =moment(this.textTimeValue,timeFormat).format('HH:mm')
                    this.dateTime = this.textDateValue + ' ' + this.textTimeValue
                }
            }
        },
        reset (){
            this.date = null
            this.time = null
            this.textTimeValue = ''
            this.textDateValue = ''
        }
    }

}
</script>
