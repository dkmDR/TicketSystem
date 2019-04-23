import React, { Component } from 'react';

import { Redirect } from 'react-router-dom';
import { partner } from '../../api/Path';
import { get } from '../../api/Requestor';

class Login extends Component {
    /** constructor */
    constructor(props){
        super(props);
        this.state = {
            user_name : "",
            password : "",
            redirect : false
        }
        this.logIn = this.logIn.bind(this);
        this.validSessionId = this.validSessionId.bind(this);
    }
    /** assign values */
    change = element => {
        let key = element.target.id;
        let value = element.target.value;
        this.setState({ [key] : value });
    }
    /** log in */
    logIn(){
        let {
            user_name,
            password
        } = this.state;
        let params = "?user="+user_name+"&pass="+password;
        get(partner.logIn, params)
        .then(response => response.json())
        .then(data => {
            if ( data != null ){
                if ( data.status ) {
                    sessionStorage.setItem('sessionId', data.sessionId);
                    this.setState({redirect : true});
                } else {
                    window.alert("You don't have permission!");
                }
            } else {
                window.alert("Data does not found!");
            }            
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    validSessionId(){
        let sessionId = sessionStorage.getItem("sessionId");
        if ( sessionId !== 'undefined' ) {
            let params = "?session=" + sessionId;
            get(partner.validSessionId, params)
            .then(response => response.json())
            .then(data => {
                if ( data != null ){
                    if ( data.status ) {
                        this.setState({redirect : true});
                    }
                }            
            })
            .catch(error=>{
                console.log("Error...");
                console.log(error);
            });
        }
    }
    componentDidMount(){
        this.validSessionId();
    }
    /** rendering... */
    render(){
        let { redirect } = this.state;        
        if ( redirect ) {
            return <Redirect to='/ticket/list' />
        }
        return (
            <div className="container">
                <div className="row">
                    <div className="col-md-5 offset-md-3 marginTop">
                        <div className="card">
                            <div className="card-header">
                                Login
                            </div>
                            <div className="card-body"> 
                                <form>
                                    <div className="row">
                                        <div className="form-group col-md-12">
                                            <label>Username/Email</label>
                                            <input type="text" id="user_name" onChange={e=>this.change(e)} className="form-control" placeholder="Enter User Name or email" />
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="form-group col-md-12">
                                            <label>Password</label>
                                            <input type="password" id="password" onChange={e=>this.change(e)} className="form-control" placeholder="Enter your password" />
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col-md-4 form-group">
                                            <button className="btn btn-success buttons" onClick={e=>this.logIn()}>Enter</button>
                                        </div>                                    
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Login;