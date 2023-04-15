// Her oprettes bruges JustValidate-biblioteket og angiver formularen med id'et #signup som parameter
const validation = new JustValidate("#signup");

// Her tilføjes feltvalidering til navnefeltet med id'et #name
validation
.addField("#name", [
{
rule: "required" // Kræver, at feltet er udfyldt
}
])

// Her tilføjes feltvalidering til e-mailfeltet med id'et #email
.addField("#email", [
{
rule: "required" // Kræver, at feltet er udfyldt
},
{
rule: "email" // Kræver, at feltet indeholder en gyldig e-mailadresse
},
{
validator: (value) => () => {
// Validerer e-mailen asynkront ved at foretage en GET-anmodning til validate-email.php og tilføje værdien af e-mailfeltet som en parameter i URL'en
return fetch("validate-email.php?email=" + encodeURIComponent(value))
.then(function(response) {
return response.json();
})
.then(function(json) {
return json.available;
});
},
errorMessage: "Email is already used" // Viser en fejlbesked, hvis e-mailen allerede er i brug
}
])

// Her tilføjes feltvalidering til adgangskodefeltet med id'et #password
.addField("#password", [
{
rule: "required" // Kræver, at feltet er udfyldt
},
{
rule: "password" // Kræver, at feltet indeholder en gyldig adgangskode
}
])

// Her tilføjes feltvalidering til bekræft adgangskodefeltet med id'et #password_confirmation
.addField("#password_confirmation", [
{
validator: (value, fields) => {
// Validerer, at bekræft adgangskodefeltet indeholder samme værdi som adgangskodefeltet
return value === fields["#password"].elem.value;
},
errorMessage: "Passwords does not match" // Viser en fejlbesked, hvis adgangskoderne ikke matcher
}
])

// Her tilføjes en funktion, der kaldes, når valideringen er succesfuld
.onSuccess((event) => {
// Sender formularen med id'et #signup
document.getElementById("signup").submit();
});