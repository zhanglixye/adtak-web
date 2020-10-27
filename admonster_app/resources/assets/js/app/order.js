import camelCase from 'lodash/camelCase';
import upperFirst from 'lodash/upperFirst';
import store from '../stores';
import Vue from '../base';

// ./components/Templates配下のコンポーネントをグローバル登録
let requireComponent = require.context('../components/Templates/Order', true, /\w+\.vue$/);
let vueComponentArray = [];

requireComponent.keys().forEach(fileName => {
    let componentConfig = requireComponent(fileName);
    let componentName = upperFirst(
        camelCase(
            fileName.split('/').pop().replace(/\.\w+$/, '')
        )
    );
    // コンポーネント名の重複があればエラーとする
    if ( vueComponentArray.indexOf(componentName) >= 0 ) {
        throw new Error('component duplication error.');
    } else {
        vueComponentArray.push(componentName);
        Vue.component(componentName, componentConfig.default || componentConfig);
    }
});

new Vue({
    el: '#app',
    store
});
