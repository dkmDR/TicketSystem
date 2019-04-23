import React, { Component } from 'react';

import { Link, Redirect } from 'react-router-dom';
import { partner } from '../../api/Path';
import { get } from '../../api/Requestor';

class Menu extends Component {
    constructor(props){
        super(props);
        this.state = {
            redirect : false
        }
        this.logOut = this.logOut.bind(this);
    }
    /** log out... */
    logOut(){
        let sessionId = sessionStorage.getItem("sessionId"),
            params = "?sessionid="+sessionId;
        get(partner.closeSession, params)
            .then(response => response.json())
            .then(data => {                
                sessionStorage.setItem("sessionId", undefined);
                this.setState({redirect : true});
            })
            .catch(error=>{
                console.log("Error...");
                console.log(error);
            });        
    }
    /** rendering... */
    render(){
        let sessionId = sessionStorage.getItem("sessionId");        
        if ( sessionId === 'undefined' ) {
            return <Redirect to='/' />
        }
        let selected = this.props.select;
        let tab = "nav-link",
            tab1 = tab,
            tab2 = tab,
            tab3 = tab;
        switch(selected){
            case "ticket":
                tab1 += " active";
                break;
            case "employee":
                tab2 += " active";
                break;
            case "reports":
                tab3 += " active";
                break;
            default:
                break;
        }
        return (
            <div className="container-fluid containers">
                <div className="row">
                    <div className="col">
                        <button type="button" onClick={e=>this.logOut()} className="btn btn-light float-right">Log out</button>
                    </div>
                </div>
                <div className="row">
                    <div className="col">
                        <ul className="nav nav-pills">
                            <li className="nav-item" key={1}>                            
                                <Link to="/ticket/list" className={tab1}>Tickets <i className="fa fa-ticket"></i></Link>
                            </li>
                            <li className="nav-item" key={2}>
                                <Link to="/employee/list" className={tab2}>Employees <i className="fa fa-users"></i></Link>
                            </li>
                            <li className="nav-item" key={3}>
                                <Link to="/report" className={tab3}>Reports <i className="fa fa-file-text-o"></i></Link>
                            </li>                            
                        </ul>
                    </div>
                </div>
            </div>
        );
    }
}

export default Menu;