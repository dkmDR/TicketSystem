// function that returns true if value is email, false otherwise
export function verifyEmail(value){
    var emailRex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (emailRex.test(value)) {
      return true;
    }
    return false;
}
// function that verifies if a string has a given length or not
export function verifyLength(value, length) {
    if (value.length >= length) {
      return true;
    }
    return false;
}
// function that verifies if two strings are equal
export function compare(string1, string2) {
    if (string1 === string2) {
      return true;
    }
    return false;
}
// function that verifies if value contains only numbers
export function verifyNumber(value) {
    var numberRex = new RegExp("^[0-9]+$");
    if (numberRex.test(value)) {
      return true;
    }
    return false;
}
// verifies if value is a valid URL
export function verifyUrl(value) {
    try {
      new URL(value);
      return true;
    } catch (_) {
      return false;
    }
}
// verify if empty
export function empty(value){
  if (value.length > 0) {
    return false;
  }
  return true;
}