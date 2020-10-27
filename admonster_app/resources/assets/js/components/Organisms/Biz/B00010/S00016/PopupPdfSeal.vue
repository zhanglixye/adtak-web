<template>
    <v-dialog v-model="dialog" width="400" persistent>
        <div class="popup-box">
            <p class="popup-title mb-4">{{message}}</p>
            <div class="btn-box mt-3">
                <v-btn
                    color="#00BF99"
                    dark
                    style="width: 100px;"
                    class="ma-0 mr-4"
                    @click.stop="jumpEmail"
                >
                    はい
                </v-btn>
                <v-btn
                    color="#949394"
                    dark
                    style="width: 100px;"
                    class="ma-0"
                    @click.stop="dialog = false"
                >
                    いいえ
                </v-btn>
            </div>
        </div>
    </v-dialog>
</template>

<script>
export default {
    name: 'PopupPdfSeal',
    data() {
        return {
            dialog: false,
            message: '押印なしで続行しますか'
        }
    },
    methods: {
        async jumpEmail() {
            this.$parent.loadingDisplay(true);
            let parameter = this.$parent.axiosFormData;
            let item_array = JSON.parse(JSON.stringify(this.$parent.pdfList));  //复制pdf列表数组
            for (let i = 0; i < item_array.length; i++) {
                item_array[i].stamp_before.file_data = '';
                item_array[i].stamp_after.file_data = '';
            }
            parameter.set('task_result_content', JSON.stringify({item_array: item_array}));
            let res = await axios.post('/api/biz/b00010/s00016/tmpSave', parameter);
            if (res.data.result === 'success') {
                this.$parent.$parent.AdmittedToJump = false;
                this.$parent.$parent.defaultEmailStatus = true;
                this.dialog = false;
            } else {
                this.$parent.$refs.alert.show(res.data.err_message);
            }
        },
    }
}
</script>

<style scoped>
    p {
        margin: 0;
    }

    .popup-box {
        width: 400px;
        background-color: #ffffff;
        margin: 0 auto;
        padding: 35px 50px;
    }

    .popup-title {
        text-align: center;
        font-size: 18px;
        line-height: 27px;
        color: #555555;
        word-wrap: break-word;
    }

    .btn-box {
        display: flex;
        justify-content: center;
    }
</style>
