import React, { Component } from 'react';
import TicketInfoHead from './TicketInfoHead';
import { Redirect } from 'react-router-dom';
import Menu from '../Layout/Menu';
import TicketInfoFoot from './TIcketInfoFoot';
import { ticket } from '../../api/Path';
import { get } from '../../api/Requestor';

class TicketView extends Component{
    constructor(props){
        super(props);
        this.state = {
            redirect : false,
            ticketId : 0,
            redirectToEdit : false
        }
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
                        this.setState({redirect:true});
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
    edit = ticket => {
        this.setState({ticketId : ticket, redirectToEdit: true});
    }
    /** rendering.... */
    render(){
        let ticketId = 0,
        updating = false,
        ref = this,
        {
            redirect,
            redirectToEdit
        } = this.state;
        if(this.props.location.state !== undefined){
            ticketId = this.props.location.state.ticket;
            updating = this.props.location.state.updating;
        }        
        if ( ticketId === 0 ) {
            return <Redirect to='/ticket/list' />
        }
        if ( redirect ) {
            return <Redirect to='/ticket/list' />
        }
        if ( redirectToEdit ) {
            return <Redirect to={{ pathname : '/ticket', state : { ticketId : ticketId } }} />
        }
        return [
            <Menu select="ticket" key={1} />,
            <div className="container-fluid" key={2}>
                <div className="row">
                    <div className="col">
                        {
                            updating === true ?
                            <div className="padding">
                                <button className="btn btn-success actionButton" onClick={e=>ref.edit(ticketId)}><i className="fa fa-edit"></i> Edit</button>
                                <button className="btn btn-danger" onClick={e=>ref.remove(ticketId)}><i className="fa fa-trash"></i> Delete</button>
                            </div>
                            :
                            ""
                        }                        
                    </div>
                </div>
            </div>,
            <TicketInfoHead ticketId={ticketId} key={3} />,
            <TicketInfoFoot ticketId={ticketId} updating={updating} key={4} />
        ];
    }
}

export default TicketView;