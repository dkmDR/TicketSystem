/** POST */
export function request( json, path, method ) {
    method = typeof method !== "undefined" ? method : "POST";
    return fetch(
        process.env.REACT_APP_API_PATH + path,
        {
            method : method,
            headers : { 'Content-Type': 'application/json' },
            body: JSON.stringify( json )
        }
    )
}
/** GET */
export function get( path, value ){
    if ( typeof value !== "undefined" )
        return fetch( process.env.REACT_APP_API_PATH + path + value );

    return fetch( process.env.REACT_APP_API_PATH + path );
}
/** PUT, DELETE */
export function exec( path, value ){
    if ( typeof value !== "undefined" )
        return fetch( process.env.REACT_APP_API_PATH + path + value );

    return fetch( process.env.REACT_APP_API_PATH + path );
}