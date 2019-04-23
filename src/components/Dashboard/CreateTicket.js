import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import DateTime from 'react-datetime';
import Select from 'react-select';
import { Link, Redirect } from 'react-router-dom';
import { partner, miscellaneous, ticket } from '../../api/Path';
import { request, get } from '../../api/Requestor';
import { empty } from '../../form/Validation';
/*import moment from 'moment';*/

class CreateTicket extends Component {    
    constructor(props){
        super(props);
        this.state = {
            ticket_id : 0,
            ticket_date : "",
            subject : "",
            status_id : 0,
            status_des : "",
            ticket_description : "",
            ticket_note : "",
            status : [],
            employees : [],
            employeesSelected : null,
            sendRequest : false,
            errorMessage : false,
            messageLabel : "",
            subjectState : "",
            subjectStateLabel : "Enter a subject ticket",
            dateState : "",
            dateStateLabel : "Due date ticket",
            statusStateLabel : "Select a status...",
            employeesStateLabel : "Select Employee(s)",
            descriptionState : "",
            descriptionStateLabel : ""
        }
        this.searchEmployees = this.searchEmployees.bind(this);
        this.searchStatus = this.searchStatus.bind(this);
        this.create = this.create.bind(this);
    }    
    /** assign values */
    change = element => {
        let key = element.target.id;
        let value = element.target.value;
        this.setState({ [key] : value });
    }
    /** search statuses */
    searchStatus(){
        get(miscellaneous.state)
        .then(response => response.json())
        .then(data => {
            this.setState({status : data});
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }    
    /** search all employees that not removed */
    searchEmployees(){
        get(partner.options)
        .then(response => response.json())
        .then(data => {
            if ( data != null )
                this.setState({employees : data});
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    /** search */
    search = ticketId => {
        let params = "?ticket="+ticketId;
        get(ticket.getInfo,params)
        .then(response => response.json())
        .then(res => {
            if ( res != null ){
                let data = res.ticket,
                assignedInfo = res.assigned;                                
                this.setState({
                    ticket_id : data.id,
                    subject : data.subject,
                    ticket_date : data.ticket_date,
                    ticket_description : data.description,
                    status_id : data.status_id,
                    status_des : data.status_des,
                    employeesStateLabel : assignedInfo.label,
                    employeesSelected : res.employees
                    });
            } else {
                window.alert("Response does not found");
            }
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
        });
    }
    /** create ticket */
    create(){
        let {
            ticket_id,
            subject,  
            ticket_date,
            status_id,
            ticket_description,
            employeesSelected   
        } = this.state
        let validation = this.validation();
        if ( validation ) {
            let object = {
                ticket_id : ticket_id,
                subject : subject,
                ticket_date : ticket_date,
                status_id : status_id,
                ticket_description : ticket_description,
                employees : employeesSelected,
                sessionId : sessionStorage.getItem("sessionId")
            }
            request(object, ticket.create)
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
    /** validation... */
    validation(){
        let {
            subject,  
            ticket_date,
            status_id,
            ticket_description,
            employeesSelected   
        } = this.state
        if ( empty(subject) ) {
            this.setState({subjectState : "required", subjectStateLabel : "Enter a valid subject"});
            return false;
        }
        this.setState({subjectState : "", subjectStateLabel : ""});
        if ( ticket_date.length !== undefined ) {
            this.setState({dateState: "required", dateStateLabel: "Enter a valid date"});
            return false;
        }
        this.setState({dateState: "", dateStateLabel: ""});
        if ( status_id < 1 ) {
            this.setState({statusStateLabel: "Select at least one status"});
            return false;
        }        
        this.setState({statusStateLabel: ""});
        if( employeesSelected == null ){
            this.setState({employeesStateLabel:"Select at least one employee"});
            return false;
        }
        this.setState({employeesStateLabel:""});
        if ( empty( ticket_description ) ){
            this.setState({descriptionState:"required",descriptionStateLabel:"Description could not be empty"});
        }
        this.setState({descriptionState:"",descriptionStateLabel:""});
        return true;
    }
    /** before render */
    componentDidMount(){
        this.searchStatus();
        this.searchEmployees();

        if(this.props.location.state !== undefined){
            this.search(this.props.location.state.ticketId);
        }
    }
    /** vakud date... */
    /*validDate = currentDate => {        
        if ( currentDate < moment() )
            return false;

        return true;
    }*/
    /** rendering... */
    render(){   
        let {
            ticket_id,
            subject,
            ticket_date,
            ticket_description,
            status_des,
            status,
            employees,
            errorMessage,
            sendRequest,
            messageLabel,
            redirect,
            subjectState,
            subjectStateLabel,
            dateState,
            dateStateLabel,
            statusStateLabel,
            employeesStateLabel,
            descriptionState,
            descriptionStateLabel
        } = this.state;  
        if ( redirect ){
            return <Redirect to={{ pathname : '/ticket/list', state : { newRecord : true } }} />
        }
        if ( ticket_id > 0 ) {
            subjectStateLabel = subject;
            dateStateLabel = ticket_date;
            statusStateLabel = status_des;
            descriptionStateLabel = ticket_description;
        }
        return [
            <Menu select="ticket" key={1} />,
            <div className="container-fluid" key={2}>
                <div className="row">
                    <div className="col">
                    <div className="card">
                        <div className="card-header">
                            Ticket Form
                        </div>
                        <div className="card-body">                            
                            <div className="container-fluid">
                                <div className="row">
                                    <div className="col">
                                        { ticket_id > 0 ? <h3>Ticket#{ticket_id}</h3> : "" }                                        
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col">
                                        {
                                            ( ticket_id > 0 ) ? <div className="alert alert-info" role="alert"> You can fill just input that you want modify! </div> : ""
                                        }
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-8 form-group">
                                        <label>Subject</label>
                                        <input type="text" id="subject" className={`form-control ${subjectState}`} placeholder={subjectStateLabel} onChange={e=>this.change(e)} />
                                    </div>                                    
                                </div>
                                <div className="row">   
                                    <div className="col-md-4 form-group">
                                        <label>Date</label>
                                        <DateTime
                                            inputProps={{                                                
                                                className: `form-control ${dateState}`,
                                                placeholder: `${dateStateLabel}`
                                            }}
                                            /*timeFormat={false}*/
                                            /*dateFormat="YYYY-MM-DD h:mm:ss a"*/
                                            dateFormat="YYYY-MM-DD"
                                            onChange={value=>this.setState({ticket_date : value})}
                                            /*isValidDate={this.validDate}*/
                                            /> 
                                    </div>                                 
                                    <div className="col-md-4 form-group">
                                        <label>Status</label>
                                        <Select
                                            className="react-select primary"
                                            classNamePrefix="react-select"
                                            onChange={object => this.setState({ status_id: object.value }) }
                                            options={status}
                                            placeholder={statusStateLabel}
                                        />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-8 form-group">
                                        <label>Employee(s)</label>
                                        <Select
                                            className="react-select info"
                                            classNamePrefix="react-select"
                                            placeholder={employeesStateLabel}
                                            closeMenuOnSelect={false}
                                            isMulti
                                            onChange={value => this.setState({ employeesSelected: value }) }
                                            options={employees}
                                        />
                                    </div>                                    
                                </div>
                                <div className="row">
                                    <div className="col-md-8 form-group">
                                        <label>Description</label>
                                        <textarea className={`form-control ${descriptionState}`} id="ticket_description" onChange={e=>this.change(e)} rows={5} placeholder={descriptionStateLabel}></textarea>
                                    </div>                                    
                                </div>                                
                                {
                                    ( !sendRequest ) ?
                                    [
                                    <div className="row" key={3}>
                                        <div className="col-md-4 form-group">
                                            <button className="btn btn-success buttons" onClick={this.create}><i className="fa fa-plus"></i> Save</button>
                                            <Link to="/ticket/list" className="btn btn-danger buttons buttonMarginLeft"><i className="fa fa-sign-out"></i> Cancel</Link>
                                        </div>                                            
                                    </div>,
                                    <div className="row" key={4}>
                                        <div className="col">
                                            {
                                                (errorMessage) ?
                                                    <span className="text-danger">{messageLabel}</span>
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
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        ];
    }
}

export default CreateTicket;