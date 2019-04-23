# Project Title

Ticket System

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

You must install NodeJS go to https://nodejs.org/en/ and download version 10.15.3 LTS

Download the project from https://github.com/dkmDR/TicketSystem.git

Install one of the following programs

*[xampp](https://www.apachefriends.org/es/index.html)

or

*[wamp](http://www.wampserver.com/en/)

or

*[laragon](https://laragon.org/)

#Version

Version PHP >= 5.6
Version Mysql >= 5.7
Node 10.15.3 LTS
NPM

### Installing

install nodeJS version 10.15.3 LTS & npm

Into the project there is a folder with PHP API (ticketAPI)

You must put this directory into apache folder

Like

```
c:/xampp/htdocs

or

c:/wamp/www
```

Make sure that apache service & mysql service are runnning...

If you are running your apache service in a domain other than localhost, 127.0.0.1, localhost: 8080 you should go to the configuration file (ticketAPI/lib/Configuration.json) and add the domain to the key (localservers)

## Database

Create mysql database with the name (ticketsystem)

You must import database file from (./backup/db_ticketsystem.sql)

## Running the tests

If you complete the before steps just do the next instruction

Go to the CMD on windows or terminal on linux

Go to project folder

like

c:/Users/UserName/Documents/ReactProject/TicketSystem

Run

```
npm install -g serve

then

serve -s build

It should be show you a little box with this information

Serving!

- Local: http://localhost:5000
- On Your Network: http://IP:5000

Copied local address to clipboard!
```

## Deployment

Default user to access to application

username : admin
password : 123456

## Built With

* [CrowPHP](http://www.crowphp.dkmsys.com) - The web framework used (Created by myself!)
* [Npm](https://www.npmjs.com/) - Dependency Management
* [React](https://reactjs.org/) - From-End Library

## Authors

* **Miguel Peralta** - *Initial work* - [TicketSystem](https://github.com/dkmDR/TicketSystem.git)

## Notes

Database files are into ./backup
