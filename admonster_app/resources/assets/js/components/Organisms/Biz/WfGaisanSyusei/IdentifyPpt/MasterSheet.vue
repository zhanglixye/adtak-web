<template>
    <div id="master-sheet-block">
        <div class="master-data-wrap">
            <div class="master-data" style="display:flex;">
                <div class="master-key pa-2">JOBNO</div>
                <div class="master-value pa-2">{{ jobno }}</div>
                <div class="master-key pa-2">開始日</div>
                <div class="master-value pa-2">{{ masterData.from }}</div>
                <div class="master-key pa-2">終了日</div>
                <div class="master-value pa-2">{{ masterData.to }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        masterData: Object,
        jobno: String
    }
}
</script>
