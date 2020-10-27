<template>
    <div :style="{height:height}">
        <v-card style="height: 100%">
            <v-form class="pa-3 ma-0 flex_column" style="height: 100%">
                <div style="text-align: center;position: relative;">
                    <div>
                        <v-btn
                            v-show="index!==0"
                            depressed
                            small
                            fab
                            @click="index = index-1"
                            class="ma-0"
                            style="width: 30px;height: 30px;"
                        >
                            <v-icon style="font-size: 24px;color: #666666;">mdi-chevron-left</v-icon>
                        </v-btn>
                        <span
                            style="display: -webkit-inline-flex; margin: 0 10px; transform: translateY(1px);"
                            :title="pdfList[index].stamp_after.file_name"
                        >{{pdfName}}</span>
                        <v-btn
                            v-show="index!==pdfList.length-1"
                            depressed
                            small
                            fab
                            @click="index = index+1"
                            class="ma-0"
                            style="width: 30px;height: 30px;"
                        >
                            <v-icon style="font-size: 24px;color: #666666;">mdi-chevron-right</v-icon>
                        </v-btn>
                    </div>
                    <div
                        class="pdf-function-box"
                        v-show="pdfShowState"
                    >
                        <v-btn
                            icon
                            class="pdf-function-btn"
                            @click="pdfBtnBox = !pdfBtnBox"
                        >
                            <v-icon>mdi-dots-horizontal</v-icon>
                        </v-btn>
                        <ul v-show="pdfBtnBox">
                            <li @click="scaleS">PDF拡大</li>
                            <li @click="scaleT">PDF縮小</li>
                            <li @click="printPdf">PDF印刷</li>
                            <li @click="download">PDFダウンロード</li>
                            <li @click="pdfReset">押印クリア</li>
                        </ul>
                    </div>
                </div>
                <v-carousel
                    v-show="pdfShowState"
                    :cycle="false"
                    light
                    height="520px"
                    hide-delimiters
                    hide-controls
                    class="mt-2"
                    style="position:relative;"
                    v-model="index">
                    <v-carousel-item ref="pdf_box" class="pdf-box" v-for="(item, i) in pdfList" :key="i">
                        <div class="father">
                            <pdf
                                ref="wrapper"
                                class="pdf-view"
                                :src="item.stamp_after.file_data"
                                :page="currentPage"
                                @num-pages="getAllPdfPageCount($event)"
                                @loaded="loadPdfHandler"
                            />
                            <div class="box" v-show="sealState">
                                <img :src="getHttpImg" class="img-sign">
                                <div class="scaleImg"></div>
                            </div>
                        </div>
                    </v-carousel-item>
                    <div class="paging-box" v-show="pdfShowState">
                        <span class="btn" @click="changePdfPage(0)">&lt;</span>
                        <span class="page-num">{{currentPage}}/{{pageCount}}</span>
                        <span class="btn" @click="changePdfPage(1)">&gt;</span>
                    </div>
                </v-carousel>
                <div
                    v-show="!pdfShowState"
                    class="pdf-prompt-message"
                >
                    PDF形式の添付ファイルはありません
                </div>
                <div class="row ma-0" style="position: absolute;bottom: 16px;width: 100%">
                    <v-btn
                        color="#949394"
                        dark
                        style="width: 100px;"
                        class="ma-0 mr-3"
                        @click="backTasks"
                    >
                        戻る
                    </v-btn>
                    <v-btn
                        color="warning"
                        style="width: 100px;"
                        class="ma-0 mr-3"
                        @click="preserveBtn"
                        :disabled="!pdfShowState"
                    >
                        保留
                    </v-btn>
                    <v-btn
                        v-show="showPositiveBtn && !sealState"
                        class="ma-0 mr-3"
                        color="success"
                        style="width: 100px"
                        @click="sealState = !sealState"
                        :disabled="!pdfShowState"
                    >
                        判子表示
                    </v-btn>
                    <v-btn
                        v-show="showPositiveBtn && sealState"
                        class="ma-0 mr-3"
                        color="success"
                        style="width: 100px"
                        @click="sealBtn"
                        :disabled="!pdfShowState"
                    >
                        押印
                    </v-btn>
                    <v-btn
                        v-show="showPositiveBtn"
                        class="ma-0 mr-3"
                        color="info"
                        style="width: 100px"
                        @click="jumpEmail"
                    >
                        メール作成
                    </v-btn>
                </div>
            </v-form>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <popup-pdf-seal ref="popupPdfSeal"></popup-pdf-seal>
    </div>
</template>

<script>
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import PopupPdfSeal from '../../../../Organisms/Biz/B00010/S00016/PopupPdfSeal';
//引入 vue-pdf 依赖
import pdf from 'vue-pdf'
export default {
    components: {AlertDialog, pdf, PopupPdfSeal},
    props: {
        heightExpand: Number,
        initData: {type: Object, require: true},
        loadingDisplay: {type: Function, require: true},
        axiosFormData: {type: FormData, require: true}
    },
    data: () => {
        return {
            //PDF列表
            pdfList: [
                {
                    stamp_after: {
                        seq_no: null,
                        file_name: null,
                        file_path: null,
                        file_size: null,
                        file_data: null
                    },
                    stamp_before: {
                        file_data: '',
                        file_name: '',
                        file_path: '',
                        file_size: null,
                        seq_no: null
                    }
                }
            ],
            //当前PDF处理过后文件名
            pdfName: '',
            //当前显示的PDF索引值
            index: 0,
            //设置按钮隐藏
            showPositiveBtn: true,
            scaleD: 100,
            getPositionData: null,
            getSealImg: null,
            //判断pdf列表内是否有内容，false为没有
            pdfShowState: false,
            //PDF功能按钮控制器
            pdfBtnBox: false,
            //押印图章显示状态
            sealState: false,
            //当前pdf文件页码
            currentPage: 0,
            //当前pdf文件总页数
            pageCount: 0,
            //所有pdf文件的总页数
            allPageCount: []
        }
    },
    computed: {
        height: function () {
            return (this.heightExpand === 0) ? '100%' : this.heightExpand + 'px';
        }
    },
    watch: {
        index: function () {
            //更换 pdf 的时候，同步更换当前pdf文件总页数
            this.pageCount = this.allPageCount[this.index];
            //更换 pdf 的时候，重置当前 pdf 的文件页码为第一页
            this.currentPage = 1;
            //每次切换文件名后执行一次文件名处理函数
            this.getPdfName();
        }
    },
    created() {
        //初始化PDF列表
        this.getPdfList();
        //请求img图片
        this.getHttpImg();
    },
    mounted() {
        this.svgMove(); //盖戳图标移动事件绑定
        this.setPosition(); //设置盖戳图片初始化的位置
        this.getPdfName();  //加载后执行一次文件名处理函数
    },
    methods: {
        //加载组件时获得PDF列表
        async getPdfList() {
            let arr = JSON.parse(this.initData.task_result_info.content).item_array;
            //判断PDF列表是否为空
            if (arr.length === 0) {
                this.pdfShowState = false;
            } else {
                this.pdfShowState = true;
                this.pdfList = arr;
            }
        },
        //返回按钮
        backTasks() {
            window.location.href = '/tasks';
        },
        //保留按钮
        async preserveBtn() {
            try {
                this.loadingDisplay(true);
                let parameter = this.axiosFormData;
                let item_array = JSON.parse(JSON.stringify(this.pdfList));  //复制pdf列表数组
                for (let i = 0; i < item_array.length; i++) {
                    item_array[i].stamp_before.file_data = '';
                    item_array[i].stamp_after.file_data = '';
                }
                parameter.set('task_result_content', JSON.stringify({item_array: item_array}));
                let res = await axios.post('/api/biz/b00010/s00016/tmpSave', parameter);
                if (res.data.result == 'success') {
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'));
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
            } catch (e) {
                this.$refs.alert.show(e);
                this.loadingDisplay(false)
            } finally {
                this.loadingDisplay(false)
            }
        },
        //押印按钮
        async sealBtn() {
            try {
                this.getPosition();
                this.loadingDisplay(true);
                let date = new Date();
                let time_zone = 0 - date.getTimezoneOffset() / 60;
                let parameter = new FormData();
                parameter.append('step_id', this.initData['request_info']['step_id']);
                parameter.append('request_id', this.initData['request_info']['request_id']);
                parameter.append('request_work_id', this.initData['request_info']['id']);
                parameter.append('task_id', this.initData['task_info']['id']);
                parameter.append('task_started_at', this.initData['task_started_at']);
                parameter.append('height', this.getPositionData.height);
                parameter.append('left', this.getPositionData.left);
                parameter.append('top', this.getPositionData.top);
                parameter.append('width', this.getPositionData.width);
                parameter.append('zoom', this.getPositionData.zoom);
                parameter.append('sealWidth', this.getPositionData.imgWidth);
                parameter.append('sealHeight', this.getPositionData.imgHeight);
                parameter.append('seq_no', this.pdfList[this.index].stamp_before.seq_no);
                parameter.append('time_zone', time_zone);
                parameter.append('currentPage', this.currentPage);
                var res = await axios.post('/api/biz/b00010/s00016/approvalPdf', parameter);
                if (res.data.result == 'success') {
                    this.pdfList[this.index].stamp_after = JSON.parse(res.data.data);
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
            } catch (e) {
                console.log(e);
            } finally {
                this.loadingDisplay(false);
                this.sealState = false;
            }
        },
        //邮件按钮
        async jumpEmail() {
            try {
                if (this.pdfList[0].stamp_before.seq_no == null || this.pdfSealExamine().length >= 1) {
                    let parameter = this.axiosFormData;
                    let item_array = JSON.parse(JSON.stringify(this.pdfList));  //复制pdf列表数组
                    for (let i = 0; i < item_array.length; i++) {
                        item_array[i].stamp_before.file_data = '';
                        item_array[i].stamp_after.file_data = '';
                    }
                    parameter.set('task_result_content', JSON.stringify({item_array: item_array}));
                    let res = await axios.post('/api/biz/b00010/s00016/tmpSave', parameter);
                    if (res.data.result === 'success') {
                        this.$parent.AdmittedToJump = false;
                        this.$parent.defaultEmailStatus = true;
                    } else {
                        this.$refs.alert.show(res.data.err_message);
                    }
                } else {
                    this.$refs.popupPdfSeal.dialog = true;
                }
            } catch (e) {
                console.log(e)
            } finally {
                this.loadingDisplay(false);
            }
        },
        //放大图片
        scaleS: function () {
            this.scaleD += 5;
            this.$refs.wrapper.map((currentValue) => {
                currentValue.$el.style.width = parseInt(this.scaleD) + '%';
            })
        },
        //缩小图片
        scaleT: function () {
            this.scaleD -= 5;
            this.$refs.wrapper.map((currentValue) => {
                currentValue.$el.style.width = parseInt(this.scaleD) + '%';
            })
        },
        //打印pdf
        printPdf: function () {
            this.$refs.wrapper[this.index].print();
            this.pdfBtnBox = false;
        },
        //执行PDF下载方法
        download: function () {
            this.downLoadPdf(this.pdfList[this.index].stamp_after.file_data, this.pdfList[this.index].stamp_after.file_name, 'pdf');
            this.pdfBtnBox = false;
        },
        //盖戳图标移动事件绑定
        svgMove: function () {
            // box是装图片的容器,fa是图片移动缩放的范围,scale是控制缩放的小图标
            let boxMore = $('.box');
            boxMore.map((index, box) => {
                let fa = document.getElementsByClassName('father')[index];
                let scale = document.getElementsByClassName('scaleImg')[index];
                // 图片移动效果
                box.onmousedown = function (ev) {
                    let oEvent = ev;
                    // 浏览器有一些图片的默认事件,这里要阻止
                    oEvent.preventDefault();
                    let disX = oEvent.clientX - box.offsetLeft;
                    let disY = oEvent.clientY - box.offsetTop;
                    fa.onmousemove = function (ev) {
                        oEvent = ev;
                        oEvent.preventDefault();
                        let x = oEvent.clientX - disX;
                        let y = oEvent.clientY - disY;

                        // 图形移动的边界判断
                        x = x <= 0 ? 0 : x;
                        x = x >= fa.offsetWidth - box.offsetWidth ? fa.offsetWidth - box.offsetWidth : x;
                        y = y <= 0 ? 0 : y;
                        y = y >= fa.offsetHeight - box.offsetHeight ? fa.offsetHeight - box.offsetHeight : y;
                        box.style.left = x + 'px';
                        box.style.top = y + 'px';
                    }
                    // 图形移出父盒子取消移动事件,防止移动过快触发鼠标移出事件,导致鼠标弹起事件失效
                    fa.onmouseleave = function () {
                        fa.onmousemove = null;
                        fa.onmouseup = null;
                    }
                    // 鼠标弹起后停止移动
                    fa.onmouseup = function () {
                        fa.onmousemove = null;
                        fa.onmouseup = null;
                    }
                },
                // 图片缩放效果
                scale.onmousedown = function (e) {
                    // 阻止冒泡,避免缩放时触发移动事件
                    e.stopPropagation();
                    e.preventDefault();
                    var pos = {
                        'w': box.offsetWidth,
                        'h': box.offsetHeight,
                        'x': e.clientX,
                        'y': e.clientY
                    };
                    fa.onmousemove = function (ev) {
                        ev.preventDefault();
                        // 设置图片的最小缩放为30*30
                        var w = Math.max(30, ev.clientX - pos.x + pos.w)
                        //var h = Math.max(30,ev.clientY - pos.y + pos.h)
                        var h = Math.max(30, ev.clientX - pos.x + pos.w)
                        // console.log(w,h)

                        // 设置图片的最大宽高
                        w = w >= fa.offsetWidth - box.offsetLeft ? fa.offsetWidth - box.offsetLeft : w
                        //h = h >= fa.offsetHeight-box.offsetTop ? fa.offsetHeight-box.offsetTop : h
                        h = w >= fa.offsetWidth - box.offsetLeft ? fa.offsetWidth - box.offsetLeft : w
                        box.style.width = w + 'px';
                        box.style.height = h + 'px';
                        // console.log(box.offsetWidth,box.offsetHeight)
                    }
                    fa.onmouseleave = function () {
                        fa.onmousemove = null;
                        fa.onmouseup = null;
                    }
                    fa.onmouseup = function () {
                        fa.onmousemove = null;
                        fa.onmouseup = null;
                    }
                }
            })
        },
        //获取盖戳图片位置
        getPosition: function () {
            let box = document.getElementsByClassName('box')[this.index];
            let parentHeight = $(box).prev('span').height();
            let parentWidth = $(box).prev('span').width();
            let imgHeight = $('.img-sign').eq(this.index).height();
            let imgWidth = $('.img-sign').eq(this.index).width();
            let approvePdfData = {
                height: parentHeight,
                width: parentWidth,
                left: box.style.left == '' ? 0 : Number(box.style.left.replace('px', '')),
                top: Number(box.style.top.replace('px', '')),
                zoom: this.scaleD,
                imgWidth: imgWidth,
                imgHeight: imgHeight
            };
            this.getPositionData = approvePdfData;
        },
        //设置盖戳图片初始化的位置
        setPosition: function () {
            let box = document.getElementsByClassName('box');
            for (let i = 0; i < box.length; i++) {
                box[i].style.left = '402px';
                box[i].style.top = '200px';
            }
        },
        //get请求获取icon地址
        async getHttpImg() {
            try {
                this.loadingDisplay(true);
                let date = new Date();
                let time_zone = 0 - date.getTimezoneOffset() / 60;
                let reqeust_work_id = this.initData.task_info.request_work_id;
                let task_id = this.initData.task_info.id;
                this.getHttpImg = '/api/biz/b00010/s00016/' + reqeust_work_id + '/' + task_id + '/getSeal?time_zone=' + time_zone;
            } catch (e) {
                console.log(e);
            } finally {
                this.loadingDisplay(false);
            }
        },
        //声明PDF下载的方法
        downLoadPdf: function (baseData, firstFileName, lastFileName) {
            let fileName = firstFileName;
            let bytes = atob(baseData.substring(baseData.indexOf(',') + 1)); //去掉url的头，并转换为byte
            //处理异常,将ascii码小于0的转换为大于0
            let content = new ArrayBuffer(bytes.length);
            let ia = new Uint8Array(content);
            for (let i = 0; i < bytes.length; i++) {
                ia[i] = bytes.charCodeAt(i);
            }
            let blob;
            if (lastFileName == 'pdf') {
                blob = new Blob([content], {
                    'type': 'application/pdf'
                });
            } else if (lastFileName == 'zip') {
                blob = new Blob([content], {
                    'type': 'application/zip'
                });
            } else if (lastFileName == 'txt') {
                blob = new Blob([ia], {
                    'type': 'text/plain,charset=shift-jis'
                });
            } else if (lastFileName == 'log') {
                blob = new Blob([ia], {
                    'type': 'text/plain'
                });
            } else if (lastFileName == 'xls') {
                blob = new Blob([content], {
                    'type': 'application/vnd.ms-excel'
                });
            } else {
                blob = new Blob([content], {
                    'type': 'application/excel'
                });
            }

            if (window.navigator.msSaveBlob) {
                window.navigator.msSaveBlob(blob, fileName);
                // msSaveOrOpenBlobの場合はファイルを保存せずに開ける
                window.navigator.msSaveOrOpenBlob(blob, fileName);
            } else {
                let itemA = document.createElement('a');
                itemA.href = window.URL.createObjectURL(blob);
                itemA.download = fileName;
                itemA.click();
            }
        },
        //声明PDF押印检查方法
        pdfSealExamine: function () {
            let arr = [];
            for (let i = 0; i < this.pdfList.length; i++) {
                if (this.pdfList[i].stamp_after.seq_no != null) {
                    arr.push(this.pdfList[i].stamp_after)
                }
            }
            return arr;
        },
        //PDF重置按钮方法
        async pdfReset() {
            try {
                let parameter = new FormData();
                parameter.append('step_id', this.initData['request_info']['step_id']);
                parameter.append('request_id', this.initData['request_info']['request_id']);
                parameter.append('request_work_id', this.initData['request_info']['id']);
                parameter.append('task_id', this.initData['task_info']['id']);
                parameter.append('task_started_at', this.initData['task_started_at']);
                parameter.append('after_seq_no', this.pdfList[this.index].stamp_after.seq_no);
                parameter.append('before_seq_no', this.pdfList[this.index].stamp_before.seq_no);
                let res = await axios.post('/api/biz/b00010/s00016/eraseStamp', parameter);
                if (res.data.result === 'success') {
                    this.pdfList[this.index].stamp_after = JSON.parse(res.data.data);
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
            } catch (e) {
                console.log(e)
            } finally {
                this.loadingDisplay(false);
                this.pdfBtnBox = false;
            }
        },
        //改变PDF页码,val传过来区分上一页下一页的值,0上一页,1下一页
        changePdfPage: function (val) {
            if (val === 0 && this.currentPage > 1) {
                this.currentPage--;
            }
            if (val === 1 && this.currentPage < this.pageCount) {
                this.currentPage++;
            }
        },
        //pdf加载时
        loadPdfHandler: function () {
            //在初始化时默认加载加载第一页，否则当前页无变化
            if (this.currentPage === 0) {
                this.currentPage = 1;
            }
        },
        //求所有pdf的总页数
        getAllPdfPageCount: function (num) {
            /***
                 * 1、在组件加载的时候，获取每个pdf的页数，放在 [allPageCount] 数组中
                 * 2、因为实在组件加载的时候获取pdf的页数，所以会多次执行这个方法，导致 [allPageCount] 中的值一直在增加
                 *    所以要限制这个数组的长度要与pdf列表的长度相等，所以要进行截取
                 * 3、因为 [vue-pdf] 组件原因，[allPageCount] 数组在赋值的时候开始会赋值 [undefined] 所以需要删除下标为 [undefined] 的数据
                 * 4、设置默认的pdf总页数为，当前pdf的总页数
                 */
            this.allPageCount.push(num);
            this.allPageCount.splice(this.pdfList.length);
            if (this.allPageCount.indexOf(undefined) !== -1) {
                this.allPageCount.splice(this.allPageCount.indexOf(undefined), 1);
            }
            this.pageCount = this.allPageCount[this.index];
            // console.log('所有文件的总页数',this.allPageCount);
            // console.log('当前文件的总页数',this.pageCount);
            // console.log('当前所在页数',this.currentPage);
        },
        //获取处理过后的pdf文件名
        getPdfName: function () {
            /***
             * 1、获取pdf原文件名
             * 2、根据计算字母数组符号每个字符平均宽度7.5px，汉字日文每个字符14px，文字中间缩减18像素
             * 3、循环字符串查找常规英文数字符号占从长度的数量，unicode编码小于256的为常规
             * 4、根据文件名中的英文字与中文字的数量计算文件名的总长度，如果小于450像素则完整显示，否则就使用 [fileNameHandling] 方法进行处理
             */
            let file_name = this.pdfList[this.index].stamp_after.file_name;
            if (file_name == null) {
                this.pdfName = '';
            } else {
                let big_font = [];
                let file_name_width = null;
                for (let item = 0; item < file_name.length; item++) {
                    if (file_name.charCodeAt(item) > 256) {
                        big_font.push(file_name[item])
                    }
                }
                file_name_width = ((file_name.length - big_font.length) * 7.5) + (big_font.length * 14);
                if (file_name_width < 450) {
                    this.pdfName = file_name;
                } else {
                    this.fileNameHandling(1, big_font.length, file_name.length, 450)
                }
            }
        },
        //文件名处理方法
        fileNameHandling: function (multiple, big_font_length, str_length, max_width) {
            /***
             * 逻辑：
             *    获取英文字符数与中文字符数每样各除1.1（四舍五入）计算宽度的和，再加18px，如果小于pdf预览窗口的50%
             *    就按当前比例（1.1）从中文与英文字符数量各除1.1，然后将获取的数值相加，再除2得到的数值就是要字符串前后各截取的长度
             *    如果大于最大显示范围 [max_width] ，就增加0.1的比例（1.1+0.1=1.2）然后继续重复上面的操作，直到宽度可以小于最大显示范围 [max_width]
             * 1、声明变量：原文件名/原文件名中字母的长度/原文件名中中文字符的长度/最大截取字符的长度
             * 2、文件名处理方法：
             *    注意：根据计算字母数组符号每个字符平均宽度7.5px，汉字日文每个字符14px，文字中间缩减18像素
             *    使用 do while 循环，计算范围内最大能显示多少字母以及汉字，方法如下：
             *    循环条件：缩减过后的字母长度所占像素 加上 缩减过后的汉字数量所占像素 加上18像素 要大于 最大显示范围 [max_width]
             *    在原文件字符数的基础上每次循环减少 10% 的字符长度，因此再基础倍数 [1] 上每次循环都要加上 [0.1]
             *    在同等的倍数下，同时计算 字母 与 汉字 最大可以显示多少个，因为是在同一倍数下相除，所以可以理解为是在同比例减少，获取的数据相对准确些
             *    经过循环处理得到的 [字母] 与 [汉字] 最大数量之和肯定是最接近最大显示范围的 [max_width] 如需调节精度可以修改循环内每次增加的倍数，默认为 [10%|0.1|十分之一]
             * 3、计算字符串前后最大能截取多少字符
             * 4、把处理过后的字符串赋值给 [pdfName]
             */
            let file_name = this.pdfList[this.index].stamp_after.file_name;
            let small_length = str_length - big_font_length;
            let big_length = big_font_length;
            let max_slice_length = null;
            do {
                multiple = Number((Number(multiple) + 0.1).toFixed(1));
                small_length = Number(((str_length - big_font_length) / multiple).toFixed(0));
                big_length = Number((big_font_length / multiple).toFixed(0));
            } while ((small_length * 7.5) + (big_length * 14) + 18 > max_width);
            max_slice_length = ((small_length + big_length) / 2).toFixed(0);
            this.pdfName = file_name.slice(0, max_slice_length) + '……' + file_name.slice(str_length - max_slice_length);
            /*//打印各种参数：
                console.log('倍数：',multiple);
                console.log('中文字符数：',big_font_length);
                console.log('文件名字符数：',str_length);
                console.log('最大范围：',max_width);
                console.log(str_length-big_font_length,'英文保留了：',small_length);
                console.log(big_font_length,'中文保留了：',big_length);
                console.log('前后字段截取长度：',max_slice_length);*/
        }
    }
}
</script>

<style scoped lang="scss">
    /*pdf-vue 溢出隐藏 修改为 溢出滚动*/
    .pdf-view {
        width: 100%;
    }
    .pdf-prompt-message {
        height: 516px;
        line-height: 516px;
        font-size: 22px;
        font-weight: bold;
        text-align: center;
        color: #555;
        background-color: #e5e5e5;
    }
    .scaleImg {
        width: 10px;
        height: 10px;
        overflow: hidden;
        cursor: se-resize;
        position: absolute;
        right: 0;
        bottom: 0;
        background-color: rgba(122, 191, 238, 0.5);
    }
    img {
        width: 100%;
        height: 100%;
        cursor: move;
    }
    .box {
        position: absolute;
        top: 0;
        height: 100px;
        width: 100px;
    }
    .pdf-function-box {
        position: absolute;
        right: 0;
        top: -13px;
        z-index: 100;
        .pdf-function-btn {
            position: absolute;
            right: 0;
        }
        ul {
            margin-top: 44px;
            padding: 0;
            background: #ffffff;
            border-radius: 2px;
            box-shadow: 0 3px 1px -2px rgba(0, 0, 0, .2), 0 2px 2px 0 rgba(0, 0, 0, .14), 0 1px 5px 0 rgba(0, 0, 0, .12);

            li {
                text-align: left;
                line-height: 24px;
                padding: 5px 12px 2px 12px;
                cursor: pointer;
                border-bottom: solid 1px #f2f2f2;
            }

            li:after {
                border-bottom: none;
            }

            li:hover {
                background-color: #f2f2f2;
            }
        }
    }
    .pdf-box {
        height: 100%;
    }
    .paging-box {
        position: absolute;
        left: 50%;
        bottom: 20px;
        margin-left: -65px;
        background-color: rgba(0, 0, 0, 0.1);
        padding: 5px 10px;
        border-radius: 2px;
        transition: all 0.2s;
        .page-num {
            padding: 0 8px;
        }
        .btn {
            padding: 3px 10px;
            background-color: #f2f2f2;
        }
    }
    .paging-box:hover {
        background-color: rgba(0, 0, 0, 0.6);
        color: #ffffff;
        .btn {
            color: #333333;
        }
    }
</style>
