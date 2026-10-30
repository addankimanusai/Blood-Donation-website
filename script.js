// =============================================
// Blood Donation Website - Main Script
// =============================================

// City data for Indian states
const citiesByState = {
  'Andhra Pradesh': ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool'],
  'Arunachal Pradesh': ['Itanagar', 'Naharlagun', 'Pasighat', 'Roing', 'Tezu'],
  'Assam': ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon'],
  'Bihar': ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga'],
  'Chhattisgarh': ['Raipur', 'Bhilai', 'Durg', 'Bilaspur', 'Rajnandgaon'],
  'Goa': ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda'],
  'Gujarat': ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Gandhinagar'],
  'Haryana': ['Gurgaon', 'Faridabad', 'Panipat', 'Ambala', 'Rohtak'],
  'Himachal Pradesh': ['Shimla', 'Mandi', 'Solan', 'Kullu', 'Manali'],
  'Jharkhand': ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro', 'Hazaribagh'],
  'Karnataka': ['Bangalore', 'Mysore', 'Mangalore', 'Hubli', 'Belgaum'],
  'Kerala': ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam'],
  'Madhya Pradesh': ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain'],
  'Maharashtra': ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad'],
  'Manipur': ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul'],
  'Meghalaya': ['Shillong', 'Tura', 'Jowai', 'Nongpoh', 'Williamnagar'],
  'Mizoram': ['Aizawl', 'Lunglei', 'Saiha', 'Champhai', 'Kolasib'],
  'Nagaland': ['Kohima', 'Dimapur', 'Mokokchung', 'Wokha', 'Tuensang'],
  'Odisha': ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur'],
  'Punjab': ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda'],
  'Rajasthan': ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Ajmer'],
  'Sikkim': ['Gangtok', 'Namchi', 'Gyalshing', 'Mangan', 'Soreng'],
  'Tamil Nadu': ['Chennai', 'Coimbatore', 'Madurai', 'Trichy', 'Salem'],
  'Telangana': ['Hyderabad', 'Warangal', 'Nizamabad', 'Khammam', 'Karimnagar'],
  'Tripura': ['Agartala', 'Dharmanagar', 'Udaipur', 'Kailashahar', 'Belonia'],
  'Uttar Pradesh': ['Lucknow', 'Kanpur', 'Varanasi', 'Agra', 'Allahabad'],
  'Uttarakhand': ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Rudrapur'],
  'West Bengal': ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri']
};

// Blood type compatibility data
const compatibilityChart = {
  'A+': { canDonateTo: ['A+', 'AB+'], canReceiveFrom: ['A+', 'A-', 'O+', 'O-'] },
  'A-': { canDonateTo: ['A-', 'A+', 'AB-', 'AB+'], canReceiveFrom: ['A-', 'O-'] },
  'B+': { canDonateTo: ['B+', 'AB+'], canReceiveFrom: ['B+', 'B-', 'O+', 'O-'] },
  'B-': { canDonateTo: ['B-', 'B+', 'AB-', 'AB+'], canReceiveFrom: ['B-', 'O-'] },
  'O+': { canDonateTo: ['O+', 'A+', 'B+', 'AB+'], canReceiveFrom: ['O+', 'O-'] },
  'O-': { canDonateTo: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], canReceiveFrom: ['O-'] },
  'AB+': { canDonateTo: ['AB+'], canReceiveFrom: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] },
  'AB-': { canDonateTo: ['AB-', 'AB+'], canReceiveFrom: ['A-', 'B-', 'AB-', 'O-'] }
};

// Dark mode state
let darkMode = localStorage.getItem('darkMode') === 'true';

// =============================================
// Toggle Mobile Menu
// =============================================
function toggleMenu() {
  const navLinks = document.querySelector('.nav-links');
  const menuIcon = document.querySelector('.menu-icon');
  if (navLinks && menuIcon) {
    navLinks.classList.toggle('active');
    menuIcon.classList.toggle('active');
  }
}

// =============================================
// Update City Options Based on State
// =============================================
function updateCityOptions(cityDropdownId, selectedState) {
  const cityDropdown = document.getElementById(cityDropdownId);
  if (!cityDropdown) return;

  cityDropdown.innerHTML = '<option value="">Select City</option>';

  if (selectedState && citiesByState[selectedState]) {
    citiesByState[selectedState].forEach(city => {
      const option = document.createElement('option');
      option.value = city;
      option.textContent = city;
      cityDropdown.appendChild(option);
    });
  }
}

// =============================================
// Dark Mode Toggle
// =============================================
function toggleDarkMode() {
  darkMode = !darkMode;
  document.body.classList.toggle('dark-mode', darkMode);
  localStorage.setItem('darkMode', darkMode);
  updateDarkModeButton();
}

// =============================================
// Update Dark Mode Button
// =============================================
function updateDarkModeButton() {
  const btn = document.getElementById('darkModeBtn');
  if (btn) {
    btn.textContent = darkMode ? 'Light Mode' : 'Dark Mode';
    btn.setAttribute('aria-label', darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode');
  }
}

// =============================================
// Initialize Dark Mode
// =============================================
function initDarkMode() {
  if (darkMode) {
    document.body.classList.add('dark-mode');
  }
  updateDarkModeButton();
}

// =============================================
// Form Validation
// =============================================
function validateDonationForm() {
  clearFieldErrors();
  const errors = [];

  const name = document.getElementById('name')?.value.trim();
  const age = document.getElementById('age')?.value;
  const phone = document.getElementById('phone')?.value.trim();
  const gender = document.getElementById('gender')?.value;
  const bloodGroup = document.getElementById('bloodGroup')?.value;
  const state = document.getElementById('state')?.value;
  const city = document.getElementById('city')?.value;

  if (!name || name.length < 2) {
    errors.push({ field: 'name', message: 'Name must be at least 2 characters' });
  }

  if (!age || age < 18 || age > 65) {
    errors.push({ field: 'age', message: 'Age must be between 18 and 65' });
  }

  if (!phone || !/^[0-9]{10}$/.test(phone)) {
    errors.push({ field: 'phone', message: 'Phone must be 10 digits' });
  }

  if (!gender) {
    errors.push({ field: 'gender', message: 'Please select gender' });
  }

  if (!bloodGroup) {
    errors.push({ field: 'bloodGroup', message: 'Please select blood group' });
  }

  if (!state) {
    errors.push({ field: 'state', message: 'Please select state' });
  }

  if (!city) {
    errors.push({ field: 'city', message: 'Please select city' });
  }

  return errors;
}

// =============================================
// Show Field Error
// =============================================
function showFieldError(fieldId, message) {
  const field = document.getElementById(fieldId);
  if (!field) return;

  field.classList.add('error');
  const errorDiv = document.createElement('div');
  errorDiv.className = 'field-error';
  errorDiv.textContent = message;
  field.parentElement.appendChild(errorDiv);
}

// =============================================
// Clear All Field Errors
// =============================================
function clearFieldErrors() {
  document.querySelectorAll('.field-error').forEach(el => el.remove());
  document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
}

// =============================================
// Show Message (Success/Error)
// =============================================
function showMessage(message, type = 'success') {
  const container = document.getElementById('messageContainer') || createMessageContainer();
  container.innerHTML = `<div class="message ${type}">${message}</div>`;
  container.style.display = 'block';

  setTimeout(() => {
    container.style.display = 'none';
  }, 5000);
}

// =============================================
// Create Message Container
// =============================================
function createMessageContainer() {
  const container = document.createElement('div');
  container.id = 'messageContainer';
  container.style.cssText = 'position:fixed;top:100px;left:50%;transform:translateX(-50%);z-index:9999;width:90%;max-width:500px;';
  document.body.appendChild(container);
  return container;
}

// =============================================
// AJAX Form Submission
// =============================================
async function submitDonationForm(event) {
  event.preventDefault();

  const errors = validateDonationForm();
  if (errors.length > 0) {
    errors.forEach(err => showFieldError(err.field, err.message));
    return;
  }

  const form = event.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn?.textContent;

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    submitBtn.textContent = 'Submitting...';
  }

  try {
    const formData = new FormData(form);

    const response = await fetch('submit_donation.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.text();

    if (result.includes('success') || result.includes('Thank You')) {
      showMessage('Thank you for your donation registration!', 'success');
      form.reset();
    } else {
      showMessage('There was an error. Please try again.', 'error');
    }
  } catch (error) {
    showMessage('Network error. Please check your connection.', 'error');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('loading');
      submitBtn.textContent = originalText;
    }
  }
}

// =============================================
// Search Donors (with validation)
// =============================================
function searchDonors() {
  const searchBloodGroup = document.getElementById('searchBloodGroup')?.value;
  const searchState = document.getElementById('searchState')?.value;
  const searchCity = document.getElementById('searchCity')?.value;

  if (!searchBloodGroup || !searchState || !searchCity) {
    showMessage('Please fill in all search fields', 'error');
    return;
  }

  const form = document.querySelector('#search form');
  if (form) form.submit();
}

// =============================================
// Blood Compatibility Chart
// =============================================
function renderCompatibilityChart() {
  const container = document.getElementById('compatibilityChart');
  if (!container) return;

  const bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
  let html = '<table class="compatibility-table"><thead><tr><th>Blood Type</th><th>Can Donate To</th><th>Can Receive From</th></tr></thead><tbody>';

  bloodTypes.forEach(type => {
    const data = compatibilityChart[type];
    html += `<tr>
      <td class="blood-type-cell">${type}</td>
      <td>${data.canDonateTo.join(', ')}</td>
      <td>${data.canReceiveFrom.join(', ')}</td>
    </tr>`;
  });

  html += '</tbody></table>';
  container.innerHTML = html;
}

// =============================================
// Language Translation
// =============================================
const translations = {
  en: {
    'nav.home': 'Home', 'nav.donate': 'Donate', 'nav.search': 'Search',
    'nav.about': 'About', 'nav.contact': 'Contact', 'nav.login': 'Login',
    'nav.register': 'Register', 'form.name': 'Name', 'form.age': 'Age',
    'form.phone': 'Phone', 'form.gender': 'Gender', 'form.bloodGroup': 'Blood Group',
    'form.state': 'State', 'form.city': 'City', 'form.submit': 'Submit',
    'message.success': 'Success!', 'message.error': 'Error occurred'
  },
  hi: {
    'nav.home': 'होम', 'nav.donate': 'दान करें', 'nav.search': 'खोजें',
    'nav.about': 'हमारे बारे में', 'nav.contact': 'संपर्क', 'nav.login': 'लॉग इन',
    'nav.register': 'पंजीकरण', 'form.name': 'नाम', 'form.age': 'आयु',
    'form.phone': 'फ़ोन', 'form.gender': 'लिंग', 'form.bloodGroup': 'रक्त समूह',
    'form.state': 'राज्य', 'form.city': 'शहर', 'form.submit': 'जमा करें',
    'message.success': 'सफल!', 'message.error': 'त्रुटि हुई'
  }
};

// Keep the presentation copy complete so an untranslated key is never shown to visitors.
const localizedContent = {
  en: {
    'nav.compatibility': 'Compatibility',
    'home.title': 'Donate Blood, Save Lives',
    'home.subtitle': 'Every blood donation can save up to three lives. Join us in our mission to ensure blood is available for all who need it.',
    'home.cta': 'Register as a Donor',
    'donate.title': 'Become a Donor',
    'search.title': 'Find Donors',
    'search.subtitle': 'Search for donors by blood group, state, and city.',
    'compatibility.title': 'Blood Type Compatibility',
    'compatibility.subtitle': 'Know which blood types you can donate to and receive from.',
    'about.title': 'About Us',
    'about.description': 'Blood Bond is dedicated to connecting blood donors with those in need. Our mission is to ensure that no one dies due to lack of blood.',
    'contact.title': 'Contact Us',
    'form.select': 'Select an option'
  },
  hi: {
    'nav.home': 'होम', 'nav.donate': 'रक्तदान', 'nav.search': 'खोजें', 'nav.compatibility': 'अनुकूलता',
    'nav.about': 'हमारे बारे में', 'nav.contact': 'संपर्क', 'nav.login': 'लॉग इन', 'nav.register': 'पंजीकरण',
    'home.title': 'रक्तदान करें, जीवन बचाएं',
    'home.subtitle': 'हर रक्तदान तीन लोगों तक का जीवन बचा सकता है। हमारे मिशन से जुड़ें ताकि जरूरतमंदों को रक्त मिल सके।',
    'home.cta': 'दाता के रूप में पंजीकरण करें', 'donate.title': 'रक्तदाता बनें',
    'search.title': 'रक्तदाताओं को खोजें', 'search.subtitle': 'रक्त समूह, राज्य और शहर के आधार पर दाताओं को खोजें।',
    'compatibility.title': 'रक्त समूह अनुकूलता', 'compatibility.subtitle': 'जानें कि आप किसे रक्त दे और किससे ले सकते हैं।',
    'about.title': 'हमारे बारे में', 'about.description': 'ब्लड बॉन्ड रक्तदाताओं को जरूरतमंदों से जोड़ने के लिए समर्पित है।',
    'contact.title': 'संपर्क करें', 'form.select': 'एक विकल्प चुनें', 'form.name': 'पूरा नाम', 'form.age': 'आयु',
    'form.phone': 'फोन नंबर', 'form.gender': 'लिंग', 'form.bloodGroup': 'रक्त समूह', 'form.state': 'राज्य',
    'form.city': 'शहर', 'form.submit': 'जमा करें'
  }
};

let currentLocale = localStorage.getItem('locale') || 'en';

function setLocale(locale) {
  currentLocale = locale;
  localStorage.setItem('locale', locale);
  document.documentElement.lang = locale;
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    el.textContent = localizedContent[locale]?.[key] || translations[locale]?.[key] || el.textContent;
  });
}

function toggleLanguage() {
  setLocale(currentLocale === 'en' ? 'hi' : 'en');
}

// =============================================
// Initialize on DOM Ready
// =============================================
document.addEventListener('DOMContentLoaded', function() {
  initDarkMode();
  renderCompatibilityChart();
  setLocale(currentLocale);

  // Attach donation form handler
  const donateForm = document.querySelector('#donate form');
  if (donateForm) {
    donateForm.addEventListener('submit', submitDonationForm);
  }

  // Attach state change handlers
  const stateDropdown = document.getElementById('state');
  if (stateDropdown) {
    stateDropdown.addEventListener('change', () => updateCityOptions('city', stateDropdown.value));
  }

  const searchStateDropdown = document.getElementById('searchState');
  if (searchStateDropdown) {
    searchStateDropdown.addEventListener('change', () => updateCityOptions('searchCity', searchStateDropdown.value));
  }
});
