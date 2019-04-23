import React, { Component } from 'react';
import { ticket } from '../../api/Path';
import { get } from '../../api/Requestor';

class TicketInfoHead extends Component{
    constructor(props){
        super(props);
        this.state = {
            ticketInfo : {
                            id : 0, 
                            subject : "",
                            ticket_date : "", 
                            ticket_date_format : "", 
                            created_on : "", 
                            status_id : 0,
                            status_des : "", 
                            created_by : "", 
                            description : ""
                        }
        }
        this.search = this.search.bind(this);
    }
    search(ticketId){
        let param = "?ticket=" + ticketId;
        get(ticket.get,param)
        .then(response=>response.json())
        .then(data=>{
            if( data != null){
                this.setState({ticketInfo:data});
            }
        })
        .catch(error=>{
            console.log(error);
            window.alert("Internal Error, please call the administrator");
        })
    }
    componentDidMount(){
        let { ticketId } = this.props;
        if ( ticketId !== undefined ){
            this.search(ticketId);
        }
    }
    render(){
        let { ticketInfo } = this.state;
        return (
            <div className="container-fluid">
                <div className="row">
                    <div className="col">
                        <table className="table table-condensed">
                            <tbody>
                                <tr>
                                    <td>Ticket#:</td>
                                    <td>{ticketInfo.id}</td>
                                </tr>
                                <tr>
                                    <td>Subject:</td>
                                    <td>{ticketInfo.subject}</td>
                                </tr>
                                <tr>
                                    <td>Date:</td>
                                    <td>{ticketInfo.ticket_date_format}</td>
                                </tr>
                                <tr>
                                    <td>Status:</td>
                                    <td>{ticketInfo.status_des}</td>
                                </tr>
                                <tr>
                                    <td>Created by:</td>
                                    <td>{ticketInfo.created_by}</td>
                                </tr>
                                <tr>
                                    <td>Created on:</td>
                                    <td>{ticketInfo.created_on}</td>
                                </tr>
                                <tr>
                                    <td colSpan="2">Description</td>
                                </tr>
                                <tr>
                                    <td colSpan="2">{ticketInfo.description}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        )
    }
}

export default TicketInfoHead;