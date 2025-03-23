// JavaScript Code
function updateCityOptions(cityDropdownId, selectedState) {
  const cityDropdown = document.getElementById(cityDropdownId);
  cityDropdown.innerHTML = '<option value="" disabled selected>Select City</option>';

  const citiesByState = {
    "Delhi": ["New Delhi", "Delhi"],
    "Maharashtra": ["Mumbai", "Pune", "Nagpur", "Nashik", "Aurangabad"],
    "Tamil Nadu": ["Chennai", "Coimbatore", "Madurai", "Tiruchirappalli", "Salem"],
    "Karnataka": ["Bengaluru", "Mysuru", "Hubli", "Mangalore", "Belagavi"],
    "Gujarat": ["Ahmedabad", "Surat", "Vadodara", "Rajkot", "Bhavnagar"],
    "Rajasthan": ["Jaipur", "Udaipur", "Jodhpur", "Kota", "Ajmer"],
    "Uttar Pradesh": ["Lucknow", "Kanpur", "Ghaziabad", "Agra", "Varanasi"],
    "West Bengal": ["Kolkata", "Howrah", "Darjeeling", "Durgapur", "Siliguri"],
    "Punjab": ["Amritsar", "Ludhiana", "Jalandhar", "Patiala", "Bathinda"],
    "Haryana": ["Gurgaon", "Faridabad", "Panipat", "Ambala", "Karnal"],
    "Kerala": ["Thiruvananthapuram", "Kochi", "Kozhikode", "Thrissur", "Alappuzha"],
    "Madhya Pradesh": ["Bhopal", "Indore", "Gwalior", "Jabalpur", "Ujjain"],
    "Bihar": ["Patna", "Gaya", "Bhagalpur", "Muzaffarpur", "Darbhanga"],
    "Telangana": ["Hyderabad", "Warangal", "Nizamabad", "Karimnagar", "Khammam"],
    "Andhra Pradesh": ["Vishakhapatnam", "Vijayawada", "Guntur", "Tirupati", "Nellore", "Ongole","Anathapuram"],
    "Odisha": ["Bhubaneswar", "Cuttack", "Rourkela", "Sambalpur", "Puri"],
    "Assam": ["Guwahati", "Dibrugarh", "Silchar", "Jorhat", "Tezpur"],
    "Jharkhand": ["Ranchi", "Jamshedpur", "Dhanbad", "Bokaro", "Deoghar"],
    "Chhattisgarh": ["Raipur", "Bhilai", "Bilaspur", "Korba", "Durg"],
    "Uttarakhand": ["Dehradun", "Haridwar", "Roorkee", "Nainital", "Rishikesh"],
    "Goa": ["Panaji", "Margao", "Vasco da Gama", "Mapusa", "Ponda"],
    "Himachal Pradesh": ["Shimla", "Manali", "Dharamshala", "Solan", "Mandi"],
    "Jammu and Kashmir": ["Srinagar", "Jammu", "Leh", "Baramulla", "Anantnag"],
    "Meghalaya": ["Shillong", "Tura", "Jowai", "Nongstoin", "Baghmara"],
    "Tripura": ["Agartala", "Udaipur", "Dharmanagar", "Kailashahar", "Ambassa"],
    "Nagaland": ["Kohima", "Dimapur", "Mokokchung", "Tuensang", "Wokha"],
    "Mizoram": ["Aizawl", "Lunglei", "Serchhip", "Champhai", "Lawngtlai"],
    "Manipur": ["Imphal", "Churachandpur", "Thoubal", "Bishnupur", "Ukhrul"],
    "Arunachal Pradesh": ["Itanagar", "Naharlagun", "Pasighat", "Tawang", "Ziro"],
    "Sikkim": ["Gangtok", "Namchi", "Pelling", "Gyalshing", "Rangpo"],
  };

  if (citiesByState[selectedState]) {
    citiesByState[selectedState].forEach(city => {
      const option = document.createElement("option");
      option.value = city;
      option.textContent = city;
      cityDropdown.appendChild(option);
    });
  }
}

function searchDonors() {
  // This function handles the donor search
  const searchBloodGroup = document.getElementById("searchBloodGroup").value;
  const searchState = document.getElementById("searchState").value;
  const searchCity = document.getElementById("searchCity").value;

  if (searchBloodGroup && searchState && searchCity) {
    // Construct and submit the form or perform any additional actions
    document.querySelector(".search-bar form").submit();
  } else {
    alert("Please fill in all search fields.");
  }
}
