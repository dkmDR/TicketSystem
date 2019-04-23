import Ticket from "../components/Dashboard/Ticket";
import CreateTicket from "../components/Dashboard/CreateTicket";
import Employee from "../components/Dashboard/Employee";
import Employees from "../components/Dashboard/Employees";
import Report from "../components/Dashboard/Report";
import ViewEmployee from "../components/Dashboard/ViewEmployee";
import TicketView from "../components/Dashboard/TicketView";
import TimeEntry from "../components/Dashboard/TimeEntry";
import Login from "../components/Entry/Login";

const routes = [
    {
        path : '/',
        component : Login
    },
    {
        path : '/ticket/list',
        component : Ticket
    },
    {
        path : '/ticket/entries',
        component : TimeEntry
    },
    {
        path : '/ticket',
        component : CreateTicket
    },
    {
        path : '/ticket/view',
        component : TicketView
    },
    {
        path : '/employee/list',
        component : Employees
    },    
    {
        path : '/employee',
        component : Employee
    },
    {
        path : '/view/employee',
        component : ViewEmployee
    },
    {
        path : '/report',
        component : Report
    }    
];

export default routes;