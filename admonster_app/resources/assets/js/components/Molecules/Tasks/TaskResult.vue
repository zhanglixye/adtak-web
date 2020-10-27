<template>
    <div id="task-results">
        <div id="fixed-table">
            <table>
                <thead>
                    <tr :style="{ height: trHeigth[0] }">
                        <th class="text-xs-center">
                            <span>{{ $t('common.item.name') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 作業別 -->
                    <template v-for="(item, i) in items">
                        <tr :key="item.name" :style="{ height: trHeigth[i+1] }">
                            <td>
                                <div>
                                    <span>{{ $t(step_path + '.' + item.name + '.label') }}</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <!-- 作業別 -->
                    <!-- 固定 -->
                    <template v-for="(item, i) in resultItems">
                        <tr :key="item.name" :style="{ height: trHeigth[i+1] }">
                            <td>
                                <div>
                                    <span>{{ $t('item_configs.' + item.name + '.label') }}</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <!-- 固定 -->
                </tbody>
            </table>
        </div>
        <div id="variable-table">
            <table>
                <thead>
                    <tr :style="{ height: trHeigth[0] }">
                        <th class="text-xs-center">
                            <span>作業内容</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 作業別 -->
                    <template v-for="(item, i) in items">
                        <tr :key="item.name" :style="{ height: trHeigth[i+1] }">
                            <td>
                                <item-config
                                    :item="item"
                                    :content="content"
                                    :path="step_path"
                                ></item-config>
                            </td>
                        </tr>
                    </template>
                    <!-- 作業別 -->
                    <!-- 固定 -->
                    <template v-for="(item, i) in resultItems">
                        <tr :key="item.name" :style="{ height: trHeigth[i+1] }">
                            <td>
                                <item-config
                                    :item="item"
                                    :content="content['results']"
                                    path="common.item"
                                ></item-config>
                            </td>
                        </tr>
                    </template>
                    <!-- 固定 -->
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ItemConfig from './ItemConfig'

export default {
    components: {
        ItemConfig
    },
    props: {
        content: { type: Object, require: true },
        items: { type: Array, require: true },
    },
    data: () => ({
        step_path: '',
        trHeigth: [],
    }),
    computed: {
        resultItems () {
            return [
                // { 'item_type': _const.ITEM_CONFIG_TYPE.MAP, 'name': 'type' },
                // { 'item_type': _const.ITEM_CONFIG_TYPE.STRING, 'name': 'comment' },
                // { 'item_type': _const.ITEM_CONFIG_TYPE.ARRAY_MAP, 'name': 'next_step' },
                // { 'item_type': _const.ITEM_CONFIG_TYPE.EMAIL, 'name': 'mail' },
            ]
        }
    },
    created () {
        this.step_path = 'biz.' + this.items[0].step_url.replace('/', '.')
    },
    mounted () {
        // width
        let fixedWidth = document.getElementById('fixed-table').offsetWidth
        document.getElementById('variable-table').style.width = 'calc(100% - ' + fixedWidth + 'px)'

        // height
        let fixedTr = document.querySelectorAll('#fixed-table tr')
        let variableTr = document.querySelectorAll('#variable-table tr')

        for (let i=0; i<fixedTr.length; i++) {
            let fixedTrHeight = fixedTr[i].offsetHeight
            let variableTrHeight = variableTr[i].offsetHeight

            if ( fixedTrHeight < variableTrHeight ) {
                this.trHeigth.push(variableTrHeight + 'px')
            } else {
                this.trHeigth.push(fixedTrHeight + 'px')
            }
        }
    },
}
</script>

<style scoped>
#task-results {
    height: 500px;
    background-color: #ffffff;
    overflow-y: auto;
}
#fixed-table,
#variable-table {
    float: left;
}
#fixed-table table {
    width: 200px;
}
#variable-table td {
    font-size: 13px;
}
#variable-table table {
    width: 100%;
}
table, th, td {
    color: rgba(0,0,0,.54);
    border: 1px #eeeeee solid;
    padding: 16px;
}
</style>
