import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import { Link } from 'react-router-dom';
import { partner } from '../../api/Path';
import { get } from '../../api/Requestor';

class ViewEmployee extends Component {
    constructor(props){
        super(props);
        this.state = {
            first_name : "",
            last_name : "",
            email : "",
            user_name : "",
            status_des : ""
        }
        this.searchEmployee = this.searchEmployee.bind(this);
    }
    /** search employee data */
    searchEmployee(id){
        let param = "?id=" + id;
        get(partner.searchEmployee, param)
        .then(response => response.json())
        .then(data => {
            if ( data != null ){ 
                this.setState({
                    first_name : data.first_name,
                    last_name : data.last_name,
                    email : data.email,
                    user_name : data.user_name,
                    status_des : data.status_des
                });
            }
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    componentDidMount(){
        if ( this.props.location.state !== undefined ) {
            this.searchEmployee(this.props.location.state.employee_id);
        }
    }
    render(){
        let { 
            first_name,
            last_name,
            email,
            user_name,
            status_des
         } = this.state;
        return [
            <Menu select="employee" key={1} />,
            <div className="container-fluid" key={2}>
                <div className="row">
                    <div className="col">
                        <div className="card">
                            <div className="card-header">
                                Employee View
                            </div>
                            <div className="card-body"> 
                                <div className="row">
                                    <div className="col">
                                        <h3 className="capitalize">{first_name}</h3>
                                        <label>First Name</label>
                                    </div>
                                    <div className="col">
                                        <h3 className="capitalize">{last_name}</h3>
                                        <label>Last Name</label>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col">
                                        <h3 className="lowercase">{email}</h3>
                                        <label>Email</label>
                                    </div>
                                    <div className="col">
                                        <h3 className="uppercase">{user_name}</h3>
                                        <label>User Name</label>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col">
                                        <h3 className="capitalize">{status_des}</h3>
                                        <label>Status</label>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-4 form-group">
                                        <Link to="/employee/list" className="btn btn-danger buttons buttonMarginLeft"><i className="fa fa-sign-out"></i> Back</Link> 
                                    </div>                                            
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ];
    }
}

export default ViewEmployee;