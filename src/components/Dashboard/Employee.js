import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import Select from 'react-select';
import { Link, Redirect } from 'react-router-dom';
import { partner, miscellaneous } from '../../api/Path';
import { request, get } from '../../api/Requestor';
import { empty, compare, verifyEmail } from '../../form/Validation';

class Employee extends Component {
    constructor(props){
        super(props);
        this.state = {
            employee_id : 0,
            first_name : "",
            last_name : "",
            status_id : 0,
            password : "",
            confirmPassword : "",
            email : "",
            userName : "",
            status : [],
            employee : { id : 0, first_name : "", last_name : "", email : "", user_name : "", password : "", status_id : 0, status_des : "" },
            firstNameState : "",
            firstNameStateLabel : "Enter a First Name!",
            lastNameState : "",
            lastNameStateLabel : "Enter a Last Name!",
            statusStateLabel : "Select...",
            emailState : "",
            emailStateLabel : "Enter a valid email!",
            userNameState : "",
            userNameStateLabel : "Enter a valid User Name",
            passwordState : "",
            passwordStateLabel : "Enter a valid password",
            passwordMatch : true,
            sendRequest : false,
            errorMessage : false,
            messageLabel : "",
            redirect : false
        }
        this.save = this.save.bind(this);
        this.searchStatus = this.searchStatus.bind(this);
        this.validation = this.validation.bind(this);
        this.searchEmployee = this.searchEmployee.bind(this);
    }
    /** assign values */
    change = element => {
        let key = element.target.id;
        let value = element.target.value;
        this.setState({ [key] : value });
    }
    /** search statuses */
    searchStatus(){
        get(miscellaneous.status)
        .then(response => response.json())
        .then(data => {
            this.setState({status : data});
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    /** search employee data */
    searchEmployee(id){
        let param = "?id=" + id;
        get(partner.searchEmployee, param)
        .then(response => response.json())
        .then(data => {
            if ( data != null ){ 
                this.setState({
                    employee_id : data.id,
                    first_name : data.first_name,
                    last_name : data.last_name,
                    email : data.email,
                    userName : data.user_name,
                    status_id : data.status_id,                    
                    employee : data
                });
            }
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    /** save employee */
    save(){
        let validated = this.validation();
        let {
            employee,
            first_name,
            last_name,
            status_id,
            password,
            email,
            userName            
        } = this.state;
        if ( validated )
        {
            this.setState({sendRequest : true});
            let object = {
                employee_id : employee.id,
                first_name : first_name,
                last_name : last_name,
                status_id : status_id,
                password : password,
                email : email,
                user_name : userName
            }
            request(object, partner.employee)
            .then(response => response.json())
            .then(data => {    
                if ( data.status ) {
                    this.setState({redirect : true});
                } else {
                    this.setState({sendRequest : false, errorMessage: true, messageLabel : data.message });
                }
            })
            .catch(error => {
                console.log("Error...");
                console.log(error);
                this.setState({sendRequest : false, errorMessage: true, messageLabel : "Internal Error, please call the administrator" });
            });
        }
    }
    validation(){
        let {
            first_name,
            last_name,
            status_id,
            password,            
            confirmPassword,
            email,
            userName,
            employee            
        } = this.state;
        if ( empty(first_name) ) {
            this.setState({  firstNameState : "required",  firstNameStateLabel : "First Name could not be empty!" });
            return false;
        } 
        this.setState({  firstNameState : "",  firstNameStateLabel : "" });
        if ( empty(last_name) ) {
            this.setState({ lastNameState : "required", lastNameStateLabel : "Last Name could not be empty" });
            return false;
        }
        this.setState({ lastNameState : "", lastNameStateLabel : "" });
        if ( status_id < 1 ) {
            this.setState({ statusStateLabel : "Select at least one status" });
            return false;
        }
        this.setState({ statusStateLabel : "" });        
        if ( empty(email) ) {
            this.setState({  emailState : "required",  emailStateLabel : "Email could not be empty" });
            return false;
        }
        if ( !verifyEmail(email) ) {
            this.setState({  emailState : "required",  emailStateLabel : "Email a valid email" });
            return false;
        }
        this.setState({  emailState : "",  emailStateLabel : "" });
        if ( empty(userName) ) {
            this.setState({  userNameState : "required",  userNameStateLabel : "User Name could not be empty" });
            return false;
        }        
        this.setState({  userNameState : "",  userNameStateLabel : "" });
        if( !empty(password) ) {
            this.setState({ passwordState : "", passwordStateLabel : "" });
            if ( !compare(password,confirmPassword) ) {
                this.setState({ passwordMatch : false }); 
                return false;
            } else {
                this.setState({ passwordMatch : true, errorMessage : false }); 
            }
        } else {
            /** it is updating, dont valid this */
            if ( employee.id < 1 ) {
                this.setState({ passwordState : "required", passwordStateLabel : "Please Enter the password" });
                return false;
            }
        }   
        return true;     
    }
    /** before render */
    componentDidMount(){
        this.searchStatus();
        if ( this.props.location.state !== undefined ) {
            this.searchEmployee(this.props.location.state.employee_id);
        }
    }
    /** render view */
    render(){        
        let { 
            status, 
            firstNameState,
            firstNameStateLabel,
            lastNameState,
            lastNameStateLabel,
            emailState,
            emailStateLabel,
            userNameState,
            userNameStateLabel,
            statusStateLabel,
            passwordState,
            passwordStateLabel,
            passwordMatch,
            sendRequest,
            errorMessage,
            messageLabel,
            redirect,
            employee
        } = this.state;
        if ( redirect ) {
            return <Redirect to={{ pathname : '/employee/list', state : { newRecord : true } }} />
        }
        if( employee.id > 0 ) {
            firstNameStateLabel = employee.first_name;
            lastNameStateLabel = employee.last_name;
            emailStateLabel = employee.email;
            userNameStateLabel = employee.user_name;
            statusStateLabel = employee.status_des;            
        }
        return [
            <Menu select="employee" key={1} />,
            <div className="container-fluid" key={2}>
                <div className="row">
                    <div className="col">
                        <div className="card">
                            <div className="card-header">
                                Employee Form
                            </div>
                            <div className="card-body"> 
                                <div className="row">
                                    <div className="col">
                                        {
                                            ( employee.id > 0 ) ? <div className="alert alert-info" role="alert"> You can fill just input that you want modify! </div> : ""
                                        }
                                    </div>
                                </div>
                                <form>
                                    <div className="container-fluid">
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>First Name</label>
                                                <input type="text" id="first_name" className={`form-control ${firstNameState}`} placeholder={firstNameStateLabel} onChange={e=>this.change(e)} />
                                            </div>             
                                        </div>
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>Last Name</label>
                                                <input type="text" id="last_name" className={`form-control ${lastNameState}`} placeholder={lastNameStateLabel} onChange={e=>this.change(e)} />
                                            </div>             
                                        </div>
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>Email</label>
                                                <input type="email" id="email" className={`form-control ${emailState}`} placeholder={emailStateLabel} onChange={e=>this.change(e)} />
                                            </div>             
                                        </div>
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>User Name</label>
                                                <input type="text" id="userName" className={`form-control ${userNameState}`} placeholder={userNameStateLabel} onChange={e=>this.change(e)} />
                                            </div>             
                                        </div>
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>Status</label>
                                                <Select
                                                    className="react-select primary"
                                                    classNamePrefix="react-select"
                                                    id="status_id"                                                    
                                                    onChange={object => this.setState({ status_id: object.value }) }
                                                    options={status}
                                                    placeholder={statusStateLabel}
                                                />
                                            </div>
                                        </div>                                        
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>Password</label>
                                                <input type="password" id="password" className={`form-control ${passwordState}`} placeholder={`${passwordStateLabel}`} onChange={e=>this.change(e)} />
                                            </div>             
                                        </div>
                                        <div className="row">                                            
                                            <div className="form-group col-sm-6">
                                                <label>Confirm Password</label>
                                                <input type="password" id="confirmPassword" className="form-control" onChange={e=>this.change(e)} />
                                                {
                                                 ( !passwordMatch ) ? [<br key={3} />,<span className="text-danger" key={4}>Password does not match</span>] : null
                                                }                                                
                                            </div>             
                                        </div>
                                        {
                                             ( !sendRequest ) ?
                                                [
                                                <div className="row" key={5}>
                                                    <div className="col-md-4 form-group">
                                                        <button type="button" className="btn btn-success buttons" onClick={this.save}><i className="fa fa-check"></i> Save</button>
                                                        <Link to="/employee/list" className="btn btn-danger buttons buttonMarginLeft"><i className="fa fa-sign-out"></i> Cancel</Link>                                                
                                                    </div>                                            
                                                </div>,
                                                <div className="row" key={6}>
                                                    <div className="col">
                                                        {
                                                            (errorMessage) ?
                                                                <span>{messageLabel}</span>
                                                            :
                                                            ""
                                                        }                                                        
                                                    </div>
                                                </div>
                                                ]
                                            :
                                                <div className="row">
                                                    <div className="col">
                                                        <i className="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                                                        <span>Loading please wait...</span>
                                                    </div>
                                                </div>
                                        }                                                                                
                                    </div> 
                                </form>                   
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
        ];
    }
}

export default Employee;