<template>
    <v-dialog v-model="dialog" width="400" persistent>
        <div class="popup-box">
            <p class="popup-title">{{message}}</p>
            <button class="confirm" @click.stop="dialog = false">OK</button>
        </div>
    </v-dialog>
</template>

<script>
export default {
    name: 'popupInform',
    props:{
        message:String,
    },
    data () {
        return {
            dialog: false,
        }
    },
}
</script>

<style scoped>
    p{margin: 0;}
    .popup-box{
        width: 400px;
        background-color: #ffffff;
        margin: 0 auto;
        padding: 35px 50px;
    }
    .popup-title{
        text-align: center;
        font-size: 18px;
        line-height: 27px;
        color: #555555;
        word-wrap: break-word;
    }
    .confirm{
        width: 110px;
        height: 30px;
        line-height: 30px;
        color: #ffffff;
        border-radius: 2px;
        margin: 30px auto 0 auto;
        display: block;
        outline: none;
        background-color: #00BF99;
    }
    .confirm:active{
        background-color: #03A693;
    }
</style>
