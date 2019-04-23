import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import DateTime from 'react-datetime';
import Select from 'react-select';

import { Link, Redirect } from 'react-router-dom';
import { ticket } from '../../api/Path';
import { request, get } from '../../api/Requestor';
import TicketInfoHead from './TicketInfoHead';

class TimeEntry extends Component {    
    constructor(props){
        super(props);
        this.state = {
            employee_id : 0,
            date_from : "",
            date_to : "",
            note : "",
            employees : [],
            redirect : false,
            sendRequest : false,
            errorMessage : false,
            messageLabel : ""
        }
        this.searchEmployees = this.searchEmployees.bind(this);
        this.create = this.create.bind(this);
    }  
    /** assign values */
    change = element => {
        let key = element.target.id;
        let value = element.target.value;
        this.setState({ [key] : value });
    } 
    /** search all employees that not removed */
    searchEmployees(ticketId){
        let params = '?ticket=' + ticketId;
        get(ticket.employeeOptions, params)
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
    /** create entry */
    create(){
        let {
            employee_id,
            date_from,  
            date_to,
            note
        } = this.state
        let object = {
            ticket_id : this.props.location.state.ticket,
            employee_id : employee_id,
            date_from : date_from,
            date_to : date_to,
            note : note
        }
        request(object, ticket.createEntry)
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
    /** before render */
    componentDidMount(){
        if ( this.props.location.state !== undefined ) {
            this.searchEmployees(this.props.location.state.ticket);   
        }        
    }
    /** rendering... */
    render(){ 
        if ( this.props.location.state == undefined ) {
            return <Redirect to='/ticket/list' />
        }        
        let {
            employees,
            errorMessage,
            sendRequest,
            messageLabel,
            redirect
        } = this.state;    
        if ( redirect ){
            return <Redirect to={{ pathname : '/ticket/list', state : { newRecord : true } }} />
        }
        return [
            <Menu select="ticket" key={1} />,
            <div className="container-fluid" key={2}>
                <div className="row">
                    <div className="col">
                    <div className="card">
                        <div className="card-header">
                            Ticket Entry Form
                        </div>
                        <div className="card-body">                            
                            <div className="container-fluid">
                                <div className="row"><div className="col"><h3>Ticket Information</h3></div></div>,
                                <TicketInfoHead ticketId={this.props.location.state.ticket} key={3} />
                                <div className="row"><div className="col"><h3>Time Entry Information</h3></div></div>
                                <div className="row">
                                    <div className="col-md-8 form-group">
                                        <label>Employee</label>
                                        <Select
                                            className="react-select primary"
                                            classNamePrefix="react-select"
                                            onChange={object => this.setState({ employee_id: object.value }) }
                                            options={employees}
                                            placeholder="Select Employee..."
                                        />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-4 form-group">
                                        <label>Date</label>
                                        <DateTime
                                            inputProps={{
                                                className: "form-control",
                                                placeholder: "Datetime Picker Here"
                                            }}
                                            dateFormat="YYYY-MM-DD"
                                            onChange={value=>this.setState({date_from : value})}
                                            /> 
                                    </div>
                                    <div className="col-md-4 form-group">
                                        <label>To</label>
                                        <DateTime
                                            inputProps={{
                                                className: "form-control",
                                                placeholder: "Datetime Picker Here"
                                            }}
                                            dateFormat="YYYY-MM-DD"
                                            onChange={value=>this.setState({date_to : value})}
                                            /> 
                                    </div>
                                </div>                                
                                <div className="row">
                                    <div className="col-md-8 form-group">
                                        <label>Note</label>
                                        <textarea id="note" className="form-control" rows={5} onChange={e=>this.change(e)}></textarea>
                                    </div>                                    
                                </div>                                
                                {
                                    ( !sendRequest ) ?
                                    [
                                    <div className="row" key={3}>
                                        <div className="col-md-4 form-group">
                                            <button className="btn btn-success buttons" onClick={e=>this.create()}>Save</button>
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

export default TimeEntry;