<template>
    <v-dialog v-model="dialog" persistent width="800">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('master.steps.template_edit.title') }}</v-card-title>
            <v-card-text>
                <!-- template header -->
                <c00900
                    class="mt-3 pb-3"
                    :label="$t('master.steps.before_work_template.header')"
                    :value="beforeWorkTemplate('header')"
                    :option="editorOption"
                    @input="setTemplate('header', $event)"
                ></c00900><!-- /template header -->
                <!-- template body -->
                <div v-if="Object.keys(itemConfigs).length" class="py-3">
                    <label class="v-label theme--light">{{ $t('master.steps.before_work_template.body') }}</label>
                    <template v-for="(items, key) in itemConfigs">
                        <div v-if="items.length > 1" class="px-4" :key="key">
                            <template v-for="item in items">
                                <v-checkbox
                                    v-if="item.key != item.group"
                                    :key="item.key"
                                    :input-value="isSelected(item)"
                                    :label="label(item.label_id)"
                                    primary
                                    hide-details
                                    color="primary"
                                    @change="setTemplateBody(item, $event)"
                                ></v-checkbox>
                            </template>
                        </div>
                    </template>
                </div><!-- /template body -->
                <!-- template footer -->
                <c00900
                    class="mt-3 pb-3"
                    :label="$t('master.steps.before_work_template.footer')"
                    :value="beforeWorkTemplate('footer')"
                    :option="editorOption"
                    @input="setTemplate('footer', $event)"
                ></c00900><!-- /template footer -->
            </v-card-text>

            <!-- 処理ボタン -->
            <v-card-actions class="justify-center">
                <v-btn @click="close()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="save()">{{ $t('common.button.save') }}</v-btn>
            </v-card-actions><!-- / 処理ボタン -->
            <progress-circular v-if="loading"></progress-circular>
            <notify-modal></notify-modal>
        </v-card>
    </v-dialog>
</template>

<script>
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import C00900 from '../../../Atoms/TextEditors/C00900'

export default {
    props: {
        labels: { type: Object, require: false, default: () => {} },
    },
    data: () => ({
        dialog: false,
        loading: false,
        step: {},
        template: {
            header: '',
            body: {},
            footer: '',
        },
        editorOption: {
            height: 100,
            width: '',
            valueType: 'html',
            quillOption: {},
        },
        itemConfigs: {},
    }),
    components: {
        ProgressCircular,
        NotifyModal,
        C00900,
    },
    computed: {
        beforeWorkTemplate () {
            return (key) => {
                let template = this.step.before_work_template ? JSON.parse(this.step.before_work_template) : {}
                if (key) {
                    return template[key] ? template[key] : ''
                }
                return template
            }
        },
    },
    methods: {
        show (step) {
            this.step = step
            if (step.before_work_template) {
                this.template = this.beforeWorkTemplate()//JSON.parse(step.before_work_template)
            }
            this.dialog = true
            this.setData()
        },
        save () {
            this.loading = true
            axios.post('/api/master/steps/updateRequestTemplate', {
                step_id: this.step.id,
                template: this.template,
            })
                .then((res) => {
                    // ポップアップメッセージ
                    let message = this.$t('common.message.internal_error')
                    if (res.data.result == 'success') {
                        message = this.$t('common.message.saved')
                        // propsデータ更新
                        this.$emit('set-step', res.data.step)
                    }
                    // ポップアップ表示
                    eventHub.$emit('open-notify-modal', { message: message})
                    // ダイアログ閉じる
                    this.close()
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        close () {
            Object.assign(this.$data, this.$options.data());
        },
        setData:async function () {
            this.loading = true
            axios.get('/api/master/steps/getItemConfigs', {
                params: {
                    step_id: this.step.id,
                }
            })
                .then((res) => {
                    this.itemConfigs = res.data.list
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        setTemplate (key, val) {
            this.template[key] = val
        },
        setTemplateBody (itemConfig, event) {
            let group = this.template.body[itemConfig.group] ? this.template.body[itemConfig.group] : []
            if (event) {
                group.push(itemConfig.key)
            } else {
                group = group.filter(key => key != itemConfig.key)
            }
            this.template.body[itemConfig.group] = group

            if (!group.length) {
                delete this.template.body[itemConfig.group]
            }
        },
        isSelected (itemConfig) {
            return itemConfig.group in this.template.body && this.template.body[itemConfig.group].find(key => key == itemConfig.key)
        },
        label (labelId) {
            const langCode = Vue.i18n.locale()
            return this.labels[langCode][labelId] ? this.labels[langCode][labelId] : ''
        },
    }
}
</script>

<style scoped>
.v-label {
    line-height: 20px;
    font-size: calc(12px * 0.75);
}
</style>