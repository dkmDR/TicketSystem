import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import { Link, Redirect } from 'react-router-dom';
import { ticket } from '../../api/Path';
import { get } from '../../api/Requestor';

class Ticket extends Component {
    constructor(props){
        super(props);
        this.state = {
            tickets : [],
            ticketId : 0,
            redirectToView : false,
            updating : false,
            redirectToTimeEntry : false
        }
        this.searchTickets = this.searchTickets.bind(this);
    }
    /** search tickets */
    searchTickets(){
        get(ticket.search)
        .then(response => response.json())
        .then(data => {
            if ( data != null )
                this.setState({tickets : data});
        })
        .catch(error=>{
            console.log("Error...");
            console.log(error);
            window.alert("Internal error, please call the administrator");
        });
    }
    /** remove ticket */
    remove = ticketId =>{
        let confirm = window.confirm("are you sure?");
        if ( confirm ){
            let param = "?ticket=" + ticketId;
            get(ticket.remove,param)
            .then(response=>response.json())
            .then(data=>{
                if( data != null){
                    if ( data.status )
                        this.setState({tickets:data.tickets});
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
    }
    /** view info ticket */
    view = ticket => {
        this.setState({ticketId : ticket, redirectToView : true});
    }
    /** show info ticket for update */
    update = ticket => {
        this.setState({ticketId : ticket, redirectToView : true, updating : true});
    }
    /** show entries interfaec */
    entries = ticket => {
        this.setState({ticketId : ticket, redirectToTimeEntry : true});
    }
    /** before rendering... */
    componentDidMount(){
        this.searchTickets();
    }
    /** rendering... */
    render(){ 
        let 
            { 
                tickets,
                ticketId,
                redirectToView,
                updating,
                redirectToTimeEntry
            } = this.state,
            newTicket = false,
            ref = this;
        if ( this.props.location.state !== undefined ) 
            newTicket = this.props.location.state.newRecord;       
        if ( redirectToView ) {
            return <Redirect to={{
                pathname : "/ticket/view",
                state : {ticket : ticketId, updating : updating }
            }} />
        }
        if ( redirectToTimeEntry ) {
            return <Redirect to={{
                pathname : "/ticket/entries",
                state : {ticket : ticketId }
            }} />
        }
        return [
                <Menu select="ticket" key={1} />,
                <div className="container-fluid" key={2}>
                    <div className="row">
                        <div className="col">
                        <div className="card">
                            <div className="card-header">
                                List of Tickets
                            </div>
                            <div className="card-body">
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col">
                                            {
                                                ( newTicket ) ? <div className="alert alert-success" role="alert">New Ticket has been created!</div> : ""
                                            }                                            
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col">
                                            <Link to="/ticket" className="btn btn-primary buttons float-right"><i className="fa fa-plus"></i> Create Ticket</Link>
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
                                                        <th>Subject</th>
                                                        <th>Description</th>
                                                        <th>Assigned(s)</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Created on</th>
                                                        <th>Created by</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        tickets.map(function(element,index){
                                                            return (
                                                                <tr key={index}>
                                                                    <td>{element.id}</td>
                                                                    <td>{element.subject}</td>
                                                                    <td>{element.ticket_description}</td>
                                                                    <td>{element.assigned}</td>
                                                                    <td>{element.ticket_date}</td>
                                                                    <td>{element.status_des}</td>
                                                                    <td>{element.created_on}</td>
                                                                    <td>{element.created_by}</td>
                                                                    <td>
                                                                        {
                                                                            element.status_id != 2 ?
                                                                            <div>
                                                                                <i className="fa fa-sticky-note-o actionIcon" title="Add Node & Time Entry" onClick={e=>ref.entries(element.id)}></i>                                                                                
                                                                                <i className="fa fa-pencil-square-o actionIcon" title="Edit Ticket" onClick={e=>ref.update(element.id)}></i>
                                                                                <i className="fa fa-trash actionIcon" title="Remove Ticket" onClick={e=>ref.remove(element.id)}></i>
                                                                            </div>
                                                                            : ""
                                                                        }
                                                                        <i className="fa fa-eye actionIcon" title="View Ticket" onClick={e=>ref.view(element.id)}></i>
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

export default Ticket;