import React, {Component} from 'react';
import { Redirect, Link } from 'react-router-dom';
import { ticket } from '../../api/Path';
import { get } from '../../api/Requestor';

class TicketInfoFoot extends Component {
    constructor(props){
        super(props);
        this.state = {
            employees : [],
            entries : [],
            showTab : "time-entries-tab",
            viewEmployee : false,
            employeeId : 0
        }
        this.searchEmployees = this.searchEmployees.bind(this);
        this.searchEntries = this.searchEntries.bind(this);
    }
    /** search employee list */
    searchEmployees(ticketId){
        let param = "?ticket=" + ticketId;
        get(ticket.employees,param)
        .then(response=>response.json())
        .then(data=>{
            if( data != null){
                this.setState({employees:data});
            }
        })
        .catch(error=>{
            console.log(error);
            window.alert("Internal Error, please call the administrator");
        })
    }
    /** search employee list */
    searchEntries(ticketId){
        let param = "?ticket=" + ticketId;
        get(ticket.entries,param)
        .then(response=>response.json())
        .then(data=>{
            if( data != null){
                this.setState({entries:data});
            }
        })
        .catch(error=>{
            console.log(error);
            window.alert("Internal Error, please call the administrator");
        })
    }
    /** before render... */
    componentDidMount(){
        let { ticketId } = this.props;
        if ( ticketId !== undefined ){
            this.searchEmployees(ticketId);
            this.searchEntries(ticketId);
        }
    }
    /** change view tab */
    change = event => {
        event.preventDefault();
        this.setState({showTab : event.target.id});
    }
    /** remove employee action */
    removeEmployee = assignId => {
        let param = "?record_id=" + assignId;
        get(ticket.removeAssigned,param)
        .then(response=>response.json())
        .then(data=>{
            if( data != null){
                if ( data.status )
                    this.setState({employees:data.employees});
                else
                    window.alert(data.message);
            } else {
                window.alert("Response does not found");
            }
        })
        .catch(error=>{
            console.log(error);
            window.alert("Internal Error, please call the administrator");
        })
    }
    /** remove employee action */
    removeEntry = assignId => {
        let param = "?record_id=" + assignId;
        get(ticket.removeEntry,param)
        .then(response=>response.json())
        .then(data=>{
            if( data != null){
                if ( data.status )
                    this.setState({entries:data.entries});
                else
                    window.alert(data.message);
            } else {
                window.alert("Response does not found");
            }
        })
        .catch(error=>{
            console.log(error);
            window.alert("Internal Error, please call the administrator");
        })
    }
    view = employee => {
        this.setState({employeeId : employee, viewEmployee : true});
    }
    render(){
        let {
            employees,
            entries,
            showTab,
            viewEmployee,
            employeeId
        } = this.state,
        {
            updating
        } = this.props,
        ref = this;
        if ( viewEmployee ) {
            return <Redirect to={{pathname : "/view/employee", state : {employee_id : employeeId} }} />
        }
        return (
            <div className="container-fluid">
                <div className="row">
                    <div className="col">
                        <ul className="nav nav-tabs" id="myTab" role="tablist">
                            <li className="nav-item">                            
                                <a onClick={e=>ref.change(e)} className={ showTab === "time-entries-tab" ? "nav-link active" : "nav-link" } id="time-entries-tab" data-toggle="tab" href="#time-entries" role="tab" aria-controls="time-entries" aria-selected="true">
                                    Time Entries
                                </a>
                            </li>
                            <li className="nav-item">
                                <a onClick={e=>ref.change(e)} className={ showTab === "employees-tab" ? "nav-link active" : "nav-link" } id="employees-tab" data-toggle="tab" href="#employees" role="tab" aria-controls="employees" aria-selected="false">
                                    Employees
                                </a>
                            </li>
                        </ul>
                        <div className="tab-content padding" id="myTabContent">
                            <div className={ showTab === "time-entries-tab" ? "tab-pane fade show active" : "tab-pane fade" } id="time-entries" role="tabpanel" aria-labelledby="home-tab">
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col table-response">
                                            <table className="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Created on</th>
                                                        <th>Note</th>
                                                        <th>{ updating !== false ? "Action" : "-"}</th>                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        entries.map(function(element,index){
                                                            return (
                                                                <tr key={index}>
                                                                    <td>{element.employee_name}</td>
                                                                    <td>{element.created_on}</td>
                                                                    <td>{element.note}</td>
                                                                    <td>{ updating !== false ? (<div><i className="fa fa-trash actionIcon" onClick={e=>ref.removeEntry(element.id)}></i></div>) : "-" }</td>
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
                            <div className={ showTab === "employees-tab" ? "tab-pane fade show active" : "tab-pane fade" } id="employees" role="tabpanel" aria-labelledby="profile-tab">
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col table-response">
                                            <table className="table table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th>First Name</th>
                                                        <th>Last Name</th>
                                                        <th>Email</th>
                                                        <th>{ updating !== false ? "Action" : "-"}</th>                                                                                                                
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        employees.map(function(element,index){
                                                            return (
                                                                <tr key={index}>
                                                                    <td>{element.first_name}</td>
                                                                    <td>{element.last_name}</td>
                                                                    <td>{element.email}</td>
                                                                    <td>{ updating !== false ? (<div><i className="fa fa-eye actionIcon" onClick={e=>ref.view(element.employee_id)}></i><i className="fa fa-trash actionIcon" onClick={e=>ref.removeEmployee(element.id)}></i></div>) : "-" }</td>
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
                <div className="row">
                   <div className="col">
                        <Link to="/ticket/list" className="btn btn-danger buttons buttonMarginLeft"><i className="fa fa-sign-out"></i> Cancel</Link>
                   </div>
                </div>
            </div>
        );
    }
}

export default TicketInfoFoot;