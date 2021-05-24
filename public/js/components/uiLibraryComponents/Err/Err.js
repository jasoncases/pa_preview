/**
 * Error and exceptions container object
 */
const Err = {};

Err.auth = `Incorrect Username/Password. Please try again.`;
Err.type = `Incorrect format.`;
Err.typeEmail = `Incorrect email format`;
Err.typePass = `Incorrect password format. Please try again.`;
Err.typePassMatch = `Passwords do not match. Please try again.`;
Err.typePhone = `Incorrect Phone Number format.`;
Err.textNoNumbers = `Incorrect format. No numbers in user names.`;
Err.textNoAlphas = `Inccorect format. Numbers only`;
Err.required = `Value cannot be empty.`;
Err.passwordLength8 = `Password must be at least 8 characters.`;
Err.none = '';

export default Err;
