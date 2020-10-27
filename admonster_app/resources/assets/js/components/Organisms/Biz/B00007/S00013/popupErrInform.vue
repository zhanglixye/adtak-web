<template>
    <v-dialog v-model="dialog" width="400" persistent>
        <div class="popup-box">
            <p class="popup-title">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAGCSURBVEhLtc0xS0JRFAdw9aEhYjaFSOBgEC5C0OYQrjk51eTW+wD1BRJc6wPUKlhfoKGtocXAJiuSQCqIhihCoiK9nffwPK/n/rUwvfCD9/7nf8/1KaWmCoaTBMMR8mRNZCPBcIigymSuu+l0k75nxGwoGA6x3aC6g86Wlo8EQ2D+IRZ75QfuotE3yuKiA8HQUKkc8HKmqtV92BVgKCxfWda3fOAyEOjQbEV0DTAcUCicyuVM5fNn1PEbdzQw1GygxTo661rfAMOe8Ecq1RLL3JmetZPJe8oiPJNg6Op0dvRFDp4ZOXW9ewIMyUIrEmkbi3pzmd+Gw++UJ3muMwJXqXQklzh4Dmfl8qF3X2MEJNvw+7toyW/orGp7XAM/JKByuXN0+S9UNntBOyx9p/fRY6OLjHtoxuhscs/hfZDZl0TiEV1i3EUz9hyPP1Fnjru83KdqtT10YRyqXt+VDyw2Q6FPVB7HTTD4RTuX+g/Y9jEq/ocqFk/6D0wRDCcJhpOjfD8RzufPSBLY9QAAAABJRU5ErkJggg==" alt="">
                <span>{{message}}</span>
            </p>
            <button class="confirm" @click.stop="dialog = false">OK</button>
        </div>
    </v-dialog>
</template>

<script>
export default {
    name: 'popupErrInform',
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
        color: red;
    }
    .popup-title img{
        transform: translateX(-10px);
    }
    .popup-title span{
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
