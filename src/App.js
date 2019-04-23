import React, { Component } from 'react';
// import logo from './logo.svg';
import './App.css';

import { Route, Switch, HashRouter } from 'react-router-dom';
import routes from './router/Routes';
import NotFound from './components/Dashboard/NotFound';

class App extends Component {
  render() {
    return (
      <HashRouter>
        <Switch>
          {
            routes.map(function(e,i){
              return <Route exact path={e.path} component={e.component} key={i} />
            })
          }
          <Route component={NotFound} />
        </Switch>
      </HashRouter>
    );
  }
}

export default App;
