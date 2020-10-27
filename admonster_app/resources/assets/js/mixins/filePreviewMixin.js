export default {
    data: () => ({
        isPreviewLoadings: [],
        filePreviewUrls: [],
    }),
    methods: {
        promiseOrTimeout(ms, request) {
            const timeout = new Promise((_resolve, reject) => {
                setTimeout(() => {
                    reject(Object.assign(new Error('Timed out in '+ ms + 'ms.'), {name: 'RequestTimeout', statusCode: 408 }))
                }, ms)
            })

            // Returns a race between our timeout and the passed in request
            return Promise.race([request, timeout])
        },
        setLoaded(targetId) {
            document.getElementById(targetId).setAttribute('data-loaded', true)
        },
        setInterval(key, targetId) {
            let self = this
            let retryCount = 0
            this.isPreviewLoadings[key] = setInterval(() => {
                const iframe = document.getElementById(targetId)
                if (!iframe || iframe.getAttribute('data-loaded') === 'true' || retryCount > 4) {
                    self.stopInterval(key)
                    return
                }
                const src = iframe.src;
                iframe.src = src;
                iframe.contentWindow.location.reload(true)
                retryCount++
            }, 2500)
        },
        stopInterval(key) {
            clearInterval(this.isPreviewLoadings[key])
        },
        async getFilePreviewUrl(filePath, isEmbed = false) {
            const ms = 15 * 1000  // 15sec
            let filePreviewUrl = ''
            try {
                const req = axios.post('/api/utilities/getFilePreviewUrl', {
                    file_path: filePath,
                    is_embed: isEmbed
                })
                const res = await this.promiseOrTimeout(ms, req)
                console.log({res})
                if (res.data.status > 200) {
                    throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
                }
                filePreviewUrl = res.data.file_preview_url
            } catch (e) {
                console.log(e)
                throw e
            }
            this.filePreviewUrls.push(filePreviewUrl)
            return filePreviewUrl
        },
        async openTabForFilePreview(filePath) {
            try {
                this.filePreviewLoading = true
                const url = await this.getFilePreviewUrl(filePath)

                window.open(url, '_blank')
            } catch (e) {
                if (e.statusCode === 408) {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.timeout_error') })
                } else {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                }
            } finally {
                this.filePreviewLoading = false
            }
        },
        async transitionToFilePreviewPage(filePath) {
            try {
                this.filePreviewLoading = true
                const url = await this.getFilePreviewUrl(filePath)

                window.location.href = url
            } catch (e) {
                if (e.statusCode === 408) {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.timeout_error') })
                } else {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                }
            } finally {
                this.filePreviewLoading = false
            }
        },
        async openWindowForFilePreview(filePath, opt) {
            try {
                this.filePreviewLoading = true
                const option = opt === undefined ? 'width=1000, height=700, resizable=1, top=0, left=200' : opt
                const url = await this.getFilePreviewUrl(filePath)

                window.open(url, '_blank', option)
            } catch (e) {
                if (e.statusCode === 408) {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.timeout_error') })
                } else {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                }
            } finally {
                this.filePreviewLoading = false
            }
        },
    }
}
