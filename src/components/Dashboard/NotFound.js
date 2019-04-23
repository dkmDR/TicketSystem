import React, { Component } from 'react';

import { Link } from 'react-router-dom';

class NotFound extends Component{
    render(){
        return (
            <div class="alert alert-danger" role="alert">
                Link unreachable! <br />
                <Link to="/ticket/list" className="btn btn-danger buttons buttonMarginLeft"><i className="fa fa-sign-out"></i> Back</Link> 
            </div>
        );
    }
}

export default NotFound;