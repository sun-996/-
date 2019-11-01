import Vue from 'vue'
import Router from 'vue-router';
import axios from 'axios';

Vue.use(Router);


const Login = () => import('@/views/login');
const App = () => import('@/views/app');
const Dashboard = () => import('@/views/dashboard');
const Config = () => import('@/views/config');
const Footnav = () => import('@/views/footnav');
const Classify = () => import('@/views/classify');
const Product = () => import('@/views/product');
const Admin = () => import('@/views/admin');
const Slide = () => import('@/views/slide');

const router= new Router({
  routes: [
    {
      path: '/',
      component: App,
      redirect: 'dashboard',
      children: [
        { path: 'dashboard', component: Dashboard },
        { path: 'config', component: Config },
        { path: 'footnav', component: Footnav },
        { path: 'classify', component: Classify },
        { path: 'product', component: Product },
        { path: 'admin', component: Admin },
        { path: 'slide', component: Slide }

      ]
    },
    {
      path: '/login',
      component: Login
    }


  ]
});


router.beforeEach((to ,from ,next )=>{

  let token=localStorage.getItem('sun_token');

  if(to.path !='/login'){
    if(!token){
      next('/login');
    }else{
      let param=new FormData();
      param.append('token',token);
      axios.post('/api/admin/token.php',param).then(res=>{
        if(res.data.code === 20000){
          next();
        }else{
          next('/login');
        }
      })

    }

  }else{
    next();
  }

})


export default router;
