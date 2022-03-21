import Vue from 'vue';
import 'bootstrap/dist/css/bootstrap.css';
import CheeseWhizApp from './components/CheeseWhizApp';

Vue.component('cheese-whiz-app', CheeseWhizApp);

const app = new Vue({
    el: '#cheese-app'
});
