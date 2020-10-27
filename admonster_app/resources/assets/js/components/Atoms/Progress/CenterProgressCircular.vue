<template>
    <div class="loading-wrap">
        <div class="loading-icon-wrap">
            <v-progress-circular
                    :size="50"
                    color="primary"
                    indeterminate
            ></v-progress-circular>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProgressCircular'
}
</script>

<style scoped>
    .loading-wrap{
        position: fixed;
        z-index: 2000;
        background-color: rgba(0,0,0,0.08);
        margin: 0;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        transition: opacity .3s;
        display: block;
    }
    .loading-icon-wrap{
        position:fixed;
        top: 50%;
        margin-top: -25px;
        width: 100%;
        text-align: center;
        display: block;
    }
</style>