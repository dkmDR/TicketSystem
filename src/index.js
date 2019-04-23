import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import './vendor/css/bootstrap.min.css';
import './vendor/css/react-datetime.css';
import './vendor/css/font-awesome.min.css';
// import './vendor/css/tempusdominus-bootstrap-4.min.css';
// import './vendor/js/jquery-3.3.1.min.js';
// import './vendor/js/moment.js';
// import './vendor/js/tempusdominus-bootstrap-4.min.js';
import App from './App';
import * as serviceWorker from './serviceWorker';

ReactDOM.render(<App />, document.getElementById('root'));

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
