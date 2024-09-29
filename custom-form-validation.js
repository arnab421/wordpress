// ***********old code **************
// document.addEventListener('DOMContentLoaded', function() {
//     const input = document.querySelector("#gt_phone");
//     const iti = window.intlTelInput(input, {
//         initialCountry: "us", 
//         onlyCountries: ['us' ,'ca'], 
//         separateDialCode: true, 
//         nationalMode: true,
//         utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js' 
//     }); 
    
//     function addUniqueClassesToListItems() {
//         setTimeout(function() {
           
//             const dropdown = document.querySelector('.iti__country-list');

//             if (dropdown) {
             
//                 const listItems = dropdown.querySelectorAll('.iti__country');

                
//                 listItems.forEach(function(item, index) {
//                     const uniqueClass = `custom-class-${index + 1}`; 
//                     item.classList.add(uniqueClass); 
//                 });
                
//             }
//         }, 100); 
//     }

   
//     addUniqueClassesToListItems();
    
//     document.getElementById('get_in_touch').addEventListener('submit', function(event) {
//         event.preventDefault(); 
       
//         var countryData = iti.getSelectedCountryData(); 
       
//         var countryCode = countryData.iso2;
       
//         const phoneNumber = input.value;
//         if (!iti.isValidNumber() ||  /[a-zA-Z]/.test(phoneNumber)) {
//             if(countryCode == 'us'){
//                 document.getElementById('phoneErrorus').style.display = 'block';
//                 document.getElementById('phoneErrorca').style.display = 'none';
//                 return; 
//             }
//             else if(countryCode == 'ca'){
//                 document.getElementById('phoneErrorca').style.display = 'block';
//                 document.getElementById('phoneErrorus').style.display = 'none';
//                 return; 
//             }
//         }
//         else {
//             if(countryCode == 'us'){
//                 document.getElementById('phoneErrorus').style.display = 'none';
//             }
//             else if(countryCode == 'ca'){
//                 document.getElementById('phoneErrorca').style.display = 'none';
//             }
//         }
       
//     });
//     setTimeout(function() {
//          function hideSingleElementByClassName(className) {
//             const elements = document.getElementsByClassName(className);
//              if (elements.length > 0) {
//                 elements[0].style.display = 'none';
//             }
//         }
//           hideSingleElementByClassName('custom-class-3');
//           hideSingleElementByClassName('iti__divider');
//     }, 300);
// });  

// old code   

// new code 
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector("#gt_phone");
    const iti = window.intlTelInput(input, {
        initialCountry: "us", 
        onlyCountries: ['us', 'ca'], 
        separateDialCode: true, 
        nationalMode: true,
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js' 
    });

    // Add unique classes to country list items (for dropdown custom styling)
    function addUniqueClassesToListItems() {
        setTimeout(function() {
            const dropdown = document.querySelector('.iti__country-list');
            if (dropdown) {
                const listItems = dropdown.querySelectorAll('.iti__country');
                listItems.forEach(function(item, index) {
                    const uniqueClass = `custom-class-${index + 1}`;
                    item.classList.add(uniqueClass);
                });
            }
        }, 100);
    }
    addUniqueClassesToListItems();

    // Custom validation rules for US and Canada
    function validatePhoneNumber(phoneNumber, countryCode) {
        const numericPhoneNumber = phoneNumber.replace(/\D/g, ''); // Remove non-numeric characters
        const usCanadaRegex = /^[2-9]\d{2}[2-9]\d{2}\d{4}$/; // Matches valid US/Canada numbers

        // If the number isn't 10 digits, it's invalid
        if (numericPhoneNumber.length !== 10) {
            return false;
        }

        // Apply custom validation for US/Canada phone numbers using the regex
        if (countryCode === 'us' || countryCode === 'ca') {
            if (!usCanadaRegex.test(numericPhoneNumber)) {
                return false;
            }
        }

        return true; // Number is valid if it passes the above conditions
    }

    // On form submission, validate the phone number
    document.getElementById('get_in_touch').addEventListener('submit', function(event) {
        event.preventDefault(); // Always prevent form submission initially

        var countryData = iti.getSelectedCountryData();
        var countryCode = countryData.iso2;
        const phoneNumber = iti.getNumber(); // Get the full international number

        // Validate phone number based on country
        if (!validatePhoneNumber(phoneNumber, countryCode)) {
            if (countryCode == 'us') {
                document.getElementById('phoneErrorus').style.display = 'block';
                document.getElementById('phoneErrorca').style.display = 'none';
            } else if (countryCode == 'ca') {
                document.getElementById('phoneErrorca').style.display = 'block';
                document.getElementById('phoneErrorus').style.display = 'none';
            }
            return; // Stop submission if invalid
        } else {
            // Hide error messages if valid
            document.getElementById('phoneErrorus').style.display = 'none';
            document.getElementById('phoneErrorca').style.display = 'none';
        }

        
    });

    // Hide specific elements in the dropdown (custom logic)
    setTimeout(function() {
        function hideSingleElementByClassName(className) {
            const elements = document.getElementsByClassName(className);
            if (elements.length > 0) {
                elements[0].style.display = 'none';
            }
        }
        hideSingleElementByClassName('custom-class-3');
        hideSingleElementByClassName('iti__divider');
    }, 300);
}); 

// for the second from 
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector("#gt_phone_spc");
    const iti = window.intlTelInput(input, {
        initialCountry: "us", 
        onlyCountries: ['us', 'ca'], 
        separateDialCode: true, 
        nationalMode: true,
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js' 
    });

    // Add unique classes to country list items (for dropdown custom styling)
    function addUniqueClassesToListItems() {
        setTimeout(function() {
            const dropdown = document.querySelector('.iti__country-list');
            if (dropdown) {
                const listItems = dropdown.querySelectorAll('.iti__country');
                listItems.forEach(function(item, index) {
                    const uniqueClass = `custom-class-${index + 1}`;
                    item.classList.add(uniqueClass);
                });
            }
        }, 100);
    }
    addUniqueClassesToListItems();

    // Custom validation rules for US and Canada
    function validatePhoneNumber(phoneNumber, countryCode) {
        const numericPhoneNumber = phoneNumber.replace(/\D/g, ''); // Remove non-numeric characters
        const usCanadaRegex = /^[2-9]\d{2}[2-9]\d{2}\d{4}$/; // Matches valid US/Canada numbers

        // If the number isn't 10 digits, it's invalid
        if (numericPhoneNumber.length !== 10) {
            return false;
        }

        // Apply custom validation for US/Canada phone numbers using the regex
        if (countryCode === 'us' || countryCode === 'ca') {
            if (!usCanadaRegex.test(numericPhoneNumber)) {
                return false;
            }
        }

        return true; // Number is valid if it passes the above conditions
    }

    // On form submission, validate the phone number
    document.getElementById('get_in_touch_spc').addEventListener('submit', function(event) {
        event.preventDefault(); // Always prevent form submission initially

        var countryData = iti.getSelectedCountryData();
        var countryCode = countryData.iso2;
        const phoneNumber = iti.getNumber(); // Get the full international number

        // Validate phone number based on country
        if (!validatePhoneNumber(phoneNumber, countryCode)) {
            if (countryCode == 'us') {
                document.getElementById('phoneus').style.display = 'block';
                document.getElementById('phoneca').style.display = 'none';
            } else if (countryCode == 'ca') {
                document.getElementById('phoneca').style.display = 'block';
                document.getElementById('phoneus').style.display = 'none';
            }
            return; // Stop submission if invalid
        } else {
            // Hide error messages if valid
            document.getElementById('phoneus').style.display = 'none';
            document.getElementById('phoneca').style.display = 'none';
        }

        
    });

    // Hide specific elements in the dropdown (custom logic)
    setTimeout(function() {
        function hideSingleElementByClassName(className) {
            const elements = document.getElementsByClassName(className);
            if (elements.length > 0) {
                elements[0].style.display = 'none';
            }
        }
        hideSingleElementByClassName('custom-class-3');
        hideSingleElementByClassName('iti__divider');
    }, 300);
});

// for the other from




