import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import { Link, Redirect } from 'react-router-dom';
import { exec, get } from '../../api/Requestor';
import { partner } from '../../api/Path';

class Employees extends Component {
    constructor(props){
        super(props);
        this.state = {
            employees : [],
            editEmployee : false,
            viewEmployee : false,
            employeeId : 0
        }
        this.searchEmployees = this.searchEmployees.bind(this);
    }
    /** search all employees that not removed */
    searchEmployees(){
        get(partner.searchEmployees)
        .then(response => response.json())
        .then(data => {
            this.setState({employees : data});
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    remove = key => {
        let param = "?employee="+key;
        let response = window.confirm("Are you sure?");
        if ( response == true ) {
            exec(partner.remove, param)
            .then(response => response.json())
            .then(data => {
                if ( data.status ) {
                    this.searchEmployees();
                } else {
                    window.alert(data.message);
                }
            })
            .catch(error=>{
                console.log("Error...");
                console.log(error);
            });
        }
    }
    /** edit an employee */
    edit = employee => {
        this.setState({employeeId : employee, editEmployee : true});
    }
    /** view an employee */
    view = employee => {
        this.setState({employeeId : employee, viewEmployee : true});
    }
    /** before render */
    componentDidMount(){
       this.searchEmployees();
    }
    /** rendering... */
    render(){     
        let { employees, employeeId, editEmployee, viewEmployee } = this.state,
            newEmployee = false,
            ref = this;
        if ( editEmployee ) {
            return <Redirect to={{pathname : "/employee", state : {employee_id : employeeId} }} />
        }
        if ( viewEmployee ) {
            return <Redirect to={{pathname : "/view/employee", state : {employee_id : employeeId} }} />
        }
        if ( this.props.location.state !== undefined ) 
            newEmployee = this.props.location.state.newRecord;
        return [
                <Menu select="employee" key={1} />,
                <div className="container-fluid" key={2}>
                    <div className="row">
                        <div className="col">
                        <div className="card">
                            <div className="card-header">
                                List of Employees
                            </div>
                            <div className="card-body">
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col">
                                            {
                                                ( newEmployee ) ? <div className="alert alert-success" role="alert">New Employee has been created!</div> : ""
                                            }                                            
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col">
                                            <Link to="/employee" className="btn btn-primary buttons float-right"><i className="fa fa-plus"></i> Create Employee</Link>
                                        </div>
                                    </div>
                                </div>
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col table-responsive">
                                            <table className="table table-condensed table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>First Name</th>
                                                        <th>Last Name</th>
                                                        <th>Email</th>
                                                        <th>User Name</th>
                                                        <th>Created on</th>
                                                        <th>Created by</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        employees.map(function(element,index){
                                                            return (
                                                                <tr key={index}>
                                                                    <td>{index + 1}</td>
                                                                    <td>{element.first_name}</td>
                                                                    <td>{element.last_name}</td>
                                                                    <td>{element.email}</td>
                                                                    <td>{element.user_name}</td>
                                                                    <td>{element.created_on}</td>
                                                                    <td>{element.created_by}</td>
                                                                    <td>{element.status_des}</td>
                                                                    <td>
                                                                        <i className="fa fa-eye actionIcon" title="View Employee" onClick={e=>ref.view(element.id)}></i>
                                                                        <i className="fa fa-pencil-square-o actionIcon" title="Edit Employee" onClick={e=>ref.edit(element.id)}></i>
                                                                        <i className="fa fa-trash actionIcon" title="Remove Employee" onClick={e=>ref.remove(element.id)}></i>
                                                                    </td>
                                                                </tr>
                                                            )
                                                        })
                                                    }
                                                </tbody>
                                            </table>
                                        </div>
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

export default Employees;