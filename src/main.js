import Vue from 'vue'
import router from './router';

//全局安装axios
import axios from 'axios';

Vue.prototype.$http=axios;

//@相当于在src里面
import '@/assets/css/reset.css';


import 'element-ui/lib/theme-chalk/index.css';
import ElementUI from 'element-ui';


import store from './store'

Vue.use(ElementUI);

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h('router-view')
}).$mount('#app')
