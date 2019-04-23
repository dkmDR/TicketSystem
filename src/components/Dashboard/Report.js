import React, { Component } from 'react';
import Menu from '../Layout/Menu';
import DateTime from 'react-datetime';
import { report } from '../../api/Path';
import { request } from '../../api/Requestor';

class Report extends Component {
    constructor(props){
        super(props);
        this.state = {
            date_from : "",
            date_to : "",
            entries : []
        }
    }
    search = value => {
        let {
            date_from,
            date_to
        } = this.state;
        if ( date_from.length === undefined && date_to.length === undefined ) {
            // let params = '?date_from='+date_from+'&date_to='+date_to;
            let params = {
                date_from : date_from,
                date_to : date_to
            }
            request(params,report.entries)
            .then(response => response.json())
            .then(res => {
                if ( res != null ){
                    this.setState({entries: res});
                } else {
                    window.alert("Response does not found");
                }
            })
            .catch(error=>{
                console.log("Error...");
                console.log(error);
            });
        }
    }
    render(){    
        let { entries } = this.state,
            totalHour = 0;    
        return [
                <Menu select="reports" key={1} />,
                <div className="container-fluid" key={2}>
                    <div className="row">
                        <div className="col">
                        <div className="card">
                            <div className="card-header">
                                Generate Report
                            </div>
                            <div className="card-body">
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col-md-4 form-group">
                                            <DateTime
                                                inputProps={{
                                                    className: "form-control float-right",
                                                    placeholder: "Datetime Picker Here"
                                                }}
                                                timeFormat={false}
                                                dateFormat="YYYY-MM-DD"
                                                onChange={value=>this.setState({date_from : value})}
                                                /> 
                                        </div>                               
                                        <div className="col-md-1 form-group">
                                            <label className="text-center">To</label>
                                        </div>
                                        <div className="col-md-4 form-group">
                                            <DateTime
                                                inputProps={{
                                                    className: "form-control float-right",
                                                    placeholder: "Datetime Picker Here"
                                                }}
                                                timeFormat={false}
                                                dateFormat="YYYY-MM-DD"
                                                onChange={value=>this.setState({date_to : value})}
                                                /> 
                                        </div>         
                                        <div className="col-md-2 form-group">
                                            <button className="btn btn-primary float-right" onClick={this.search}>Generate</button>
                                        </div>
                                    </div>
                                </div>
                                <div className="container-fluid">
                                    <div className="row">
                                        <div className="col">
                                            <table className="table table-condensed table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Ticket#</th>
                                                        <th>Employee</th>
                                                        <th>Note</th>
                                                        <th>Date Start</th>
                                                        <th>Date End</th>
                                                        <th>Hours</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {
                                                        entries.map(function(element,index){
                                                            {
                                                                totalHour += parseInt( element.hour_diff )
                                                            }
                                                            return (
                                                                <tr key={index}>
                                                                    <td>{element.ticket_id}</td>
                                                                    <td>{element.employee_name}</td>
                                                                    <td>{element.note}</td>
                                                                    <td>{element.date_from}</td>
                                                                    <td>{element.date_to}</td>
                                                                    <td>{element.hour_diff}</td>
                                                                </tr>
                                                            )
                                                        })
                                                    }
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colSpan="5" className="text-right">Total</th>
                                                        <th className="text-center">{totalHour}</th>
                                                    </tr>
                                                </tfoot>
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

export default Report;